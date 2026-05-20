document.addEventListener('DOMContentLoaded', function(){
  // Mobile menu toggle
  var toggle = document.getElementById('menuToggle');
  if(toggle){ toggle.addEventListener('click', function(){
    var nav = document.querySelector('.nav');
    if(nav) nav.style.display = (nav.style.display === 'flex') ? 'none' : 'flex';
  }); }

  // Load posts on home
  var loadPostsBtn = document.getElementById('loadPosts');
  if(loadPostsBtn){ loadPostsBtn.addEventListener('click', fetchPublicPosts); }

  var refreshPosts = document.getElementById('refreshPosts');
  if(refreshPosts){ refreshPosts.addEventListener('click', fetchAllPosts); }

  var refreshNotes = document.getElementById('refreshNotes');
  if(refreshNotes){ refreshNotes.addEventListener('click', fetchAllNotes); }

  var showPostFormBtn = document.getElementById('showPostFormBtn');
  if(showPostFormBtn){ showPostFormBtn.addEventListener('click', function(){ var f=document.getElementById('postForm'); if(f){ f.style.display='block'; } }); }
  var cancelPostForm = document.getElementById('cancelPostForm');
  if(cancelPostForm){ cancelPostForm.addEventListener('click', function(){ var f=document.getElementById('postForm'); if(f){ f.style.display='none'; f.reset(); } }); }
  var showNoteFormBtn = document.getElementById('showNoteFormBtn');
  if(showNoteFormBtn){ showNoteFormBtn.addEventListener('click', function(){ var f=document.getElementById('noteForm'); if(f){ f.style.display='block'; } }); }
  var cancelNoteForm = document.getElementById('cancelNoteForm');
  if(cancelNoteForm){ cancelNoteForm.addEventListener('click', function(){ var f=document.getElementById('noteForm'); if(f){ f.style.display='none'; f.reset(); } }); }

  var noteForm = document.getElementById('noteForm');
  if(noteForm){ noteForm.addEventListener('submit', function(e){ e.preventDefault(); var fd=new FormData(noteForm); var obj={}; fd.forEach(function(v,k){ obj[k]=v; }); if(obj.tags) obj.tags = obj.tags.split(',').map(s=>s.trim()).filter(Boolean); if(obj.note_id){ postJSON('/api/note/update', obj).then(function(res){ if(res && res.status==200){ noteForm.reset(); noteForm.style.display='none'; fetchAllNotes(); } else { alert(res.error||'Failed'); } }); } else { createNote(obj).then(function(res){ if(res && res.status==200){ noteForm.reset(); noteForm.style.display='none'; fetchAllNotes(); } else { alert(res.error||'Failed'); } }); } }); }

  function getStoredUser(){ try { return JSON.parse(localStorage.getItem('api_user')||null); } catch(e){ return null; } }
  function getStoredToken(){ return localStorage.getItem('api_token') || null; }
  function setStoredAuth(token,user){ if(token){ localStorage.setItem('api_token', token); } if(user){ localStorage.setItem('api_user', JSON.stringify(user)); } updateAuthUI(); }
  function clearStoredAuth(){ localStorage.removeItem('api_token'); localStorage.removeItem('api_user'); updateAuthUI(); }

  function postJSON(url, body){
    var headers = {'Content-Type':'application/json'};
    var token = getStoredToken();
    if(token){ headers['Authorization'] = 'Bearer '+token; }
    return fetch(url, {method:'POST',credentials:'same-origin',headers:headers,body:JSON.stringify(body||{})}).then(r=>r.json());
  }

  function fetchPublicPosts(){
    postJSON('/api/post/fetch-public', {}).then(renderPosts).catch(err=>console.error(err));
  }

  function fetchAllPosts(){
    postJSON('/api/post/fetch-all', {}).then(renderPosts).catch(err=>console.error(err));
  }

  function fetchAllNotes(){
    postJSON('/api/note/fetch-all', {}).then(renderNotes).catch(err=>console.error(err));
  }

  function renderPosts(data){
    var container = document.getElementById('postsList') || document.getElementById('postsContainer');
    if(!container) return;
    container.innerHTML = '';
    if(!data || !data.data) { container.innerHTML = '<div class="card">No posts found</div>'; return; }
    var currentUser = getStoredUser();
    data.data.forEach(function(p){
      var el = document.createElement('div'); el.className='card';
      var left = document.createElement('div'); left.innerHTML = '<strong>'+escapeHtml(p.title||p.name||'Untitled')+'</strong><div class="muted">'+escapeHtml(p.summary||p.short_description||'')+'</div>';
      var right = document.createElement('div'); right.innerHTML = '<small>'+ (p.created_at||'') +'</small>';
      // actions: edit/delete if owner or admin
      if(currentUser && (currentUser.role_id == 1 || currentUser.user_id == p.user_id)){
        var editBtn = document.createElement('button'); editBtn.className='btn secondary'; editBtn.textContent='Edit';
        editBtn.addEventListener('click', function(){ showEditPostForm(p); });
        var delBtn = document.createElement('button'); delBtn.className='btn'; delBtn.style.marginLeft='8px'; delBtn.textContent='Delete';
        delBtn.addEventListener('click', function(){ if(confirm('Delete this post?')) deletePost(p.post_id); });
        right.appendChild(document.createElement('br'));
        right.appendChild(editBtn);
        right.appendChild(delBtn);
      }
      el.appendChild(left); el.appendChild(right);
      container.appendChild(el);
    });
  }

  // Auth UI updater
  function updateAuthUI(){
    var user = getStoredUser();
    var guest = document.getElementById('guestLinks');
    var userLinks = document.getElementById('userLinks');
    if(guest) guest.style.display = user ? 'none' : 'flex';
    if(userLinks){
      if(user){ userLinks.style.display='flex'; userLinks.querySelector('#usernameDisplay').textContent = user.username || user.email || 'User'; }
      else { userLinks.style.display='none'; }
    }
  }

  function renderNotes(data){
    var container = document.getElementById('notesContainer');
    if(!container) return;
    container.innerHTML = '';
    if(!data || !data.data) { container.innerHTML = '<div class="card">No notes found</div>'; return; }
    var currentUser = getStoredUser();
    data.data.forEach(function(n){
      var el = document.createElement('div'); el.className='card';
      el.innerHTML = '<div><strong>'+escapeHtml(n.title||n.name||'Untitled')+'</strong><div class="muted">'+escapeHtml(n.content||n.summary||'')+'</div></div>';
      var right = document.createElement('div'); right.innerHTML = '<small>'+ (n.created_at||'') +'</small>';
      if(currentUser && (currentUser.role_id == 1 || currentUser.user_id == n.user_id)){
        var delBtn = document.createElement('button'); delBtn.className='btn'; delBtn.style.marginLeft='8px'; delBtn.textContent='Delete';
        delBtn.addEventListener('click', function(){ if(confirm('Delete this note?')) deleteNote(n.note_id); });
        right.appendChild(document.createElement('br'));
        right.appendChild(delBtn);
      }
      el.appendChild(right);
      container.appendChild(el);
    });
  }

  // Auth actions
  function loginUser(creds){ return postJSON('/api/user/login', creds).then(function(res){ if(res && res.status==200){ setStoredAuth(res.token, res.data); } return res; }); }
  function registerUser(creds){ return postJSON('/api/user/create', creds).then(function(res){ return res; }); }
  function logoutUser(){ return postJSON('/api/user/logout', {}).then(function(res){ clearStoredAuth(); return res; }); }

  // Post actions
  function createPost(payload){ return postJSON('/api/post/create', payload); }
  function updatePost(payload){ return postJSON('/api/post/update', payload); }
  function deletePost(postId){ return postJSON('/api/post/delete', {post_id: postId}).then(function(res){ if(res && res.status==200){ fetchAllPosts(); } else { alert(res.error || 'Failed'); } }); }
  function showEditPostForm(p){ var form = document.getElementById('postForm'); if(!form) return; form.style.display='block'; form.querySelector('[name="post_id"]').value = p.post_id || ''; form.querySelector('[name="title"]').value = p.title||''; form.querySelector('[name="content"]').value = p.content||''; form.querySelector('[name="category_name"]').value = p.category_name||''; form.querySelector('[name="tags"]').value = (p.tags && p.tags.join)?p.tags.join(',') : '' ; form.querySelector('[name="is_public"]').checked = (p.is_public==1); }

  // Post form submit handler
  var postForm = document.getElementById('postForm');
  if(postForm){ postForm.addEventListener('submit', function(e){ e.preventDefault(); var fd = new FormData(postForm); var obj = {}; fd.forEach(function(v,k){ obj[k]=v; }); if(obj.tags) obj.tags = obj.tags.split(',').map(s=>s.trim()).filter(Boolean); obj.is_public = postForm.querySelector('[name="is_public"]').checked ? 1 : 0; if(obj.post_id){ updatePost(obj).then(function(res){ if(res && res.status==200){ postForm.reset(); postForm.style.display='none'; fetchAllPosts(); } else { alert(res.error||'Failed'); } }); } else { createPost(obj).then(function(res){ if(res && res.status==200){ postForm.reset(); postForm.style.display='none'; fetchAllPosts(); } else { alert(res.error||'Failed'); } }); } }); }

  // Notes actions
  function createNote(payload){ return postJSON('/api/note/create', payload); }
  function deleteNote(noteId){ return postJSON('/api/note/delete', {note_id: noteId}).then(function(res){ if(res && res.status==200){ fetchAllNotes(); } else { alert(res.error || 'Failed'); } }); }

  // Login/register form handlers
  var loginForm = document.getElementById('loginForm');
  if(loginForm){ loginForm.addEventListener('submit', function(e){ e.preventDefault(); var fd=new FormData(loginForm); var creds={email:fd.get('email'), password:fd.get('password')}; loginUser(creds).then(function(res){ if(res && res.status==200){ setStoredAuth(res.token,res.data); location.href='/'; } else { alert(res.error||'Login failed'); } }); }); }

  var registerForm = document.getElementById('registerForm');
  if(registerForm){ registerForm.addEventListener('submit', function(e){ e.preventDefault(); var fd=new FormData(registerForm); var creds={username:fd.get('username'), email:fd.get('email'), password:fd.get('password'), country_id:fd.get('country_id')||null, role_id:fd.get('role_id')||null}; registerUser(creds).then(function(res){ if(res && res.status==200){ alert('Registered successfully, please login.'); location.href='/login'; } else { alert(res.error||'Registration failed'); } }); }); }

  var logoutBtn = document.getElementById('logoutBtn');
  if(logoutBtn){ logoutBtn.addEventListener('click', function(e){ e.preventDefault(); logoutUser().then(function(){ location.href='/'; }); }); }

  // Initialize auth UI
  updateAuthUI();

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]}); }
});
