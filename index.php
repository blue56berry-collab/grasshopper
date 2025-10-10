<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>CodeSprout — PHP Edition</title>
<style>
  :root{--bg:#081226;--card:#0b2233;--accent:#6ee7b7;--muted:#9fb2bf}
  *{box-sizing:border-box;font-family:Inter, system-ui, Arial}
  body{margin:0;background:linear-gradient(180deg,#031424,#071a2a);color:#eaf7f2}
  .wrap{max-width:980px;margin:40px auto;padding:20px}
  header{display:flex;justify-content:space-between;align-items:center}
  .logo{display:flex;align-items:center;gap:12px}
  .logo .mark{width:54px;height:54;border-radius:12px;background:linear-gradient(135deg,var(--accent),#60a5fa);display:flex;align-items:center;justify-content:center;font-weight:800;color:#04202a}
  .card{background:linear-gradient(180deg,rgba(255,255,255,0.03),transparent);padding:20px;border-radius:14px;box-shadow:0 8px 30px rgba(2,6,23,0.6)}
  .cta{display:flex;gap:10px;margin-top:14px}
  .btn{padding:10px 14px;border-radius:10px;border:0;cursor:pointer}
  .primary{background:var(--accent);color:#04202a}
  a{color:inherit;text-decoration:none}
  footer{margin-top:18px;color:var(--muted);font-size:13px}
</style>
</head>
<body>
  <div class="wrap">
    <header>
      <div class="logo">
        <div class="mark">CS</div>
        <div>
          <h1 style="margin:0">CodeSprout — PHP</h1>
          <div style="color:var(--muted);font-size:14px">Interactive coding lessons (PHP + JS) — internal CSS/JS in every file.</div>
        </div>
      </div>
      <nav>
        <?php if(!empty($_SESSION['user'])): ?>
          <span style="margin-right:12px;color:var(--muted)">Hi, <?php echo htmlspecialchars($_SESSION['user']); ?></span>
          <a class="btn" href="dashboard.php">Dashboard</a>
          <a class="btn" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="btn" href="auth.php">Sign Up / Login</a>
        <?php endif; ?>
      </nav>
    </header>

    <main style="margin-top:22px" class="card">
      <h2>Learn to code — interactive, beginner friendly</h2>
      <p style="color:var(--muted)">Complete lessons, run JavaScript code in the browser, and save progress to the server.</p>

      <div class="cta">
        <a class="btn primary" href="auth.php">Get Started</a>
        <a class="btn" href="lesson1.php">Try Demo Lesson</a>
      </div>
    </main>

    <footer>© <?php echo date('Y'); ?> CodeSprout — PHP Edition</footer>
  </div>
</body>
</html>
