@extends('layouts.master')

@section('content')
  <section class="hero">
    <h2>Register</h2>
    <form id="registerForm" style="max-width:520px;margin-top:12px">
      <div><label>Username</label><input name="username" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6"/></div>
      <div style="margin-top:8px"><label>Email</label><input name="email" type="email" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6"/></div>
      <div style="margin-top:8px"><label>Password</label><input name="password" type="password" required style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6"/></div>
      <div style="margin-top:8px"><label>Country (optional)</label><input name="country_id" placeholder="country id" style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6"/></div>
      <div style="margin-top:8px"><label>Role (optional)</label><input name="role_id" placeholder="role id" style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6"/></div>
      <div style="margin-top:12px"><button class="btn" type="submit">Register</button></div>
    </form>
  </section>
@endsection
