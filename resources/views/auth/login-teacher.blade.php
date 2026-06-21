<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login — RUPP SMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Hanuman:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --green:#1e4d2b; --gd:#163820; --gold:#c9a227; --accent:#1d4ed8; }
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Inter',sans-serif;min-height:100vh;display:flex;background:#f3f4f6;}
        .left{width:42%;background:linear-gradient(160deg,#1e3a5f 0%,#1d4ed8 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 40px;position:relative;overflow:hidden;}
        .left::before{content:'';position:absolute;width:400px;height:400px;border:1px solid rgba(255,255,255,0.08);border-radius:50%;top:-100px;right:-100px;}
        .left::after{content:'';position:absolute;width:280px;height:280px;border:1px solid rgba(255,255,255,0.06);border-radius:50%;bottom:-80px;left:-80px;}
        .logo{width:80px;height:80px;margin-bottom:20px;filter:drop-shadow(0 4px 12px rgba(0,0,0,.3));}
        .left h1{font-family:'Hanuman',serif;font-size:20px;font-weight:700;color:#fff;text-align:center;line-height:1.4;margin-bottom:6px;}
        .left .sub{font-size:12px;color:rgba(255,255,255,0.5);text-align:center;margin-bottom:32px;}
        .role-badge{background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);border-radius:30px;padding:8px 24px;display:flex;align-items:center;gap:10px;margin-bottom:32px;}
        .role-badge i{font-size:22px;color:#93c5fd;}
        .role-badge span{font-size:15px;font-weight:600;color:#fff;}
        .features{list-style:none;width:100%;max-width:260px;}
        .features li{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.07);font-size:12.5px;color:rgba(255,255,255,0.65);}
        .features li:last-child{border-bottom:none;}
        .features li i{color:#93c5fd;font-size:14px;flex-shrink:0;}
        .right{flex:1;display:flex;align-items:center;justify-content:center;padding:40px;}
        .card{background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:40px 40px;width:100%;max-width:400px;}
        .card-header{margin-bottom:28px;}
        .card-header .portal-tag{display:inline-flex;align-items:center;gap:6px;background:#dbeafe;color:#1e40af;border-radius:20px;padding:4px 12px;font-size:11.5px;font-weight:600;margin-bottom:12px;}
        .card-header h2{font-size:22px;font-weight:700;color:#111827;margin-bottom:4px;}
        .card-header p{font-size:13px;color:#6b7280;}
        .form-group{margin-bottom:18px;}
        .form-label{display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;}
        .input-wrap{position:relative;}
        .input-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:14px;color:#9ca3af;}
        .form-input{width:100%;padding:10px 12px 10px 36px;border:1px solid #d1d5db;border-radius:8px;font-size:13.5px;color:#111827;outline:none;font-family:'Inter',sans-serif;transition:border-color .15s,box-shadow .15s;}
        .form-input:focus{border-color:#1d4ed8;box-shadow:0 0 0 3px rgba(29,78,216,.1);}
        .form-input.err{border-color:#ef4444;}
        .alert-err{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px;}
        .remember{display:flex;align-items:center;gap:8px;font-size:13px;color:#6b7280;cursor:pointer;}
        .remember input{accent-color:#1d4ed8;}
        .btn-login{width:100%;padding:11px;background:#1d4ed8;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:background .15s;margin-top:20px;display:flex;align-items:center;justify-content:center;gap:8px;}
        .btn-login:hover{background:#1e40af;}
        .switch-links{margin-top:20px;padding-top:16px;border-top:1px solid #f3f4f6;text-align:center;font-size:12px;color:#9ca3af;}
        .switch-links a{color:#1d4ed8;text-decoration:none;font-weight:500;}
        .mobile-header{display:none;text-align:center;margin-bottom:24px;}
        .mobile-header img{width:56px;height:56px;margin-bottom:10px;}
        .mobile-header h3{font-family:"Hanuman",serif;font-size:15px;font-weight:700;color:#1e4d2b;margin-bottom:2px;}
        .mobile-header p{font-size:12px;color:#6b7280;}
        @media(max-width:768px){
            .left{display:none;}
            .right{padding:20px;}
            .mobile-header{display:block;}
            .card{padding:28px 24px;}
        }
    </style>
</head>
<body>
<div class="left">
    <img src="{{ asset('images/rupp-logo.svg') }}" alt="RUPP" class="logo">
    <h1>សាកលវិទ្យាល័យភូមិន្ទភ្នំពេញ<br>Royal University of Phnom Penh</h1>
    <p class="sub">Student Management System</p>
    <div class="role-badge">
        <i class="bi bi-person-workspace"></i>
        <span>Teacher Portal</span>
    </div>
    <ul class="features">
        <li><i class="bi bi-record-circle"></i> Record Attendance</li>
        <li><i class="bi bi-pencil-square"></i> Enter & manage grades</li>
        <li><i class="bi bi-bar-chart-line"></i> Grade reports & export</li>
        <li><i class="bi bi-people-fill"></i> Manage student groups</li>
    </ul>
</div>
<div class="right">
    <div class="card">
        <div class="mobile-header">
            <img src="{{ asset('images/rupp-logo.svg') }}" alt="RUPP">
            <h3>Royal University of Phnom Penh</h3>
            <p>Student Management System</p>
        </div>
        <div class="card-header">
            <div class="portal-tag"><i class="bi bi-person-workspace"></i> Teacher</div>
            <h2>Teacher Sign In</h2>
            <p>Sign in with your faculty credentials to access your portal.</p>
        </div>
 
        @if($errors->has('email'))
            <div class="alert-err">
                <i class="bi bi-exclamation-circle-fill"></i>
                {{ $errors->first('email') }}
            </div>
        @endif
 
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-wrap">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" class="form-input {{ $errors->has('email') ? 'err' : '' }}"
                        value="{{ old('email') }}" placeholder="teacher@university.edu" autofocus required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrap">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" class="form-input {{ $errors->has('email') ? 'err' : '' }}"
                        placeholder="Your password" required>
                </div>
            </div>
            <label class="remember">
                <input type="checkbox" name="remember"> Keep me signed in
            </label>
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Sign In as Teacher
            </button>
        </form>
 
        <div class="switch-links">
            Not a teacher?
            <a href="{{ route('student.login') }}">Student Login</a><br>
            © {{ date('Y') }} Royal University of Phnom Penh
        </div>
    </div>
</div>
</body>
</html>