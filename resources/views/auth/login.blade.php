@extends('layouts.master')

@section('content')
  <section class="hero">
    <h2>Log in</h2>
    <form id="loginForm" style="max-width:420px;margin-top:12px">
      <div><label>Email</label><input name="email" type="email" required class="input" style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6"/></div>
      <div style="margin-top:8px"><label>Password</label><input name="password" type="password" required class="input" style="width:100%;padding:8px;border-radius:6px;border:1px solid #e6e6e6"/></div>
      <div style="margin-top:12px"><button class="btn" type="submit">Log in</button></div>
    </form>
  </section>
@endsection
