<?php
session_start();
if(empty($_SESSION['user'])) { header('Location: auth.php'); exit; }
$USERS_FILE = __DIR__ . '/users.json';
$users = json_decode(@file_get_contents($USERS_FILE), true) ?: [];
$current = $_SESSION['user'];
if(!isset($users[$current])) {
  // user missing from file: create a minimal record
  $users[$current] = ['password'=>'','progress'=>[],'badges'=>[]];
  file_put_contents($USERS_FILE, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
}
$user = $users[$current];
$lessons_total = 3;
$completed = count(array_filter($user['progress'] ?? []));
$percent = round(($completed / $lessons_total) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Dashboard — CodeSprout</title>
<style>
  :root{--bg:#051426;--card:#072033;--accent:#7be6c2;--muted:#9fb7c3}
  body{margin:0;background:linear-gradient(180deg,#021525,#041726);color:#eafbf4;font-family:Inter,system-ui}
  .wrap{max-width:1100px;margin:28px auto;padding:18px}
  header{display:flex;justify-content:space-between;align-items:center}
  .avatar{width:56px;height:56;border-radius:12px;background:linear-gradient(135deg,var(--accent),#60a5fa);display:flex;align-items:center;justify-content:center;font-weight:800;color:#04202a}
  .grid{display:grid;grid-template-columns:1fr 320px;gap:18px;margin-top:18px}
  .card{background:linear-gradient(180deg,rgba(255,255,255,0.02),transparent);padding:18px;border-radius:14px}
  .lesson{display:flex;justify-content:space-between;align-items:center;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.03);margin-bottom:10px}
  .btn{padding:8px 10px;border-radius:8px;border:0;cursor:pointer}
  .play{background:var(--accent);color:#04202a}
  .progress-bar{height:12px;background:rgba(255,255,255,0.03);border-radius:999px;overflow:hidden}
  .progress-bar > i{display:block;height:100%;background:linear-gradient(90deg,var(--accent),#60a5fa)}
  .badges{display:flex;gap:8px;flex-wrap:wrap;padding:0;list-style:none;margin:10px 0 0 0}
  .badge{padding:6px 8px;border-radius:8px;background:rgba(255,255,255,0.03)}
</style>
</head>
<body>
  <div class="wrap">
    <header>
      <div>
        <h2 style="margin:0">Dashboard</h2>
        <div style="color:var(--muted)">Welcome back, <?php echo htmlspecialchars($current); ?> — keep learning!</div>
      </div>
      <div style="text-align:right">
        <div class="avatar"><?php echo strtoupper(substr($current,0,1)); ?></div>
        <div style="margin-top:8px"><a class="btn" href="logout.php">Logout</a></div>
      </div>
    </header>

    <div class="grid">
      <main class="card">
        <h3>Lessons</h3>

        <div style="margin-top:12px">
          <div class="lesson">
            <div><strong>Lesson 1 — Hello Console (JS)</strong><div style="color:var(--muted);font-size:13px">Console basics</div></div>
            <div><a class="btn play" href="lesson1.php">Open</a></div>
          </div>

          <div class="lesson">
            <div><strong>Lesson 2 — Variables & Math</strong><div style="color:var(--muted);font-size:13px">Variables & math</div></div>
            <div><a class="btn play" href="lesson2.php">Open</a></div>
          </div>

          <div class="lesson">
            <div><strong>Lesson 3 — Conditionals</strong><div style="color:var(--muted);font-size:13px">If / else</div></div>
            <div><a class="btn play" href="lesson3.php">Open</a></div>
          </div>
        </div>

        <div style="margin-top:18px">
          <h4>Overall Progress</h4>
          <div class="progress-bar" style="margin-top:8px"><i style="width: <?php echo $percent; ?>%"></i></div>
          <div style="color:var(--muted);margin-top:8px">Completed <?php echo $completed; ?> of <?php echo $lessons_total; ?> lessons</div>
        </div>
      </main>

      <aside class="card">
        <h4>Badges</h4>
        <ul class="badges">
          <?php foreach($user['badges'] ?? [] as $b): ?>
            <li class="badge"><?php echo htmlspecialchars($b); ?></li>
          <?php endforeach; ?>
          <?php if(empty($user['badges'])): ?><li style="color:var(--muted)">No badges yet — finish lessons to earn badges.</li><?php endif; ?>
        </ul>
        <div style="margin-top:14px;color:var(--muted)">Keep coding to unlock more badges!</div>
      </aside>
    </div>
  </div>
</body>
</html>
