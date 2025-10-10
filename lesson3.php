<?php
session_start();
if(empty($_SESSION['user'])) { header('Location: auth.php'); exit; }

$USERS_FILE = __DIR__ . '/users.json';
$users = json_decode(@file_get_contents($USERS_FILE), true) ?: [];
$current = $_SESSION['user'];
if(!isset($users[$current])) {
  $users[$current] = ['password'=>'','progress'=>[],'badges'=>[]];
  file_put_contents($USERS_FILE, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_progress') {
  header('Content-Type: application/json');
  $lesson = $_POST['lesson'] ?? '';
  $badge = $_POST['badge'] ?? '';
  if(!$lesson) { echo json_encode(['ok'=>false,'msg'=>'no lesson']); exit; }
  $users = json_decode(@file_get_contents($USERS_FILE), true) ?: [];
  if(!isset($users[$current])) { echo json_encode(['ok'=>false,'msg'=>'user missing']); exit; }
  $users[$current]['progress'][$lesson] = true;
  if($badge && !in_array($badge, $users[$current]['badges'])) $users[$current]['badges'][] = $badge;
  file_put_contents($USERS_FILE, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
  echo json_encode(['ok'=>true]); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Lesson 3 — Conditionals</title>
<style>
  :root{--bg:#051426;--card:#071427;--accent:#9ef0cf;--muted:#a9bdc6}
  body{margin:0;background:linear-gradient(180deg,#031426,#071428);color:#f1fbf6;font-family:Inter,system-ui}
  .wrap{max-width:980px;margin:28px auto;padding:18px}
  .card{background:linear-gradient(180deg,rgba(255,255,255,0.02),transparent);padding:16px;border-radius:12px}
  textarea{width:100%;height:200px;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.04);background:transparent;color:inherit;font-family:monospace}
  .output{height:140px;overflow:auto;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.03);background:#02131a}
  .row{display:flex;gap:8px;margin-top:10px}
  .btn{padding:10px 12px;border-radius:10px;border:0;cursor:pointer}
  .run{background:var(--accent);color:#032226}
</style>
</head>
<body>
  <div class="wrap">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div>
        <h2>Lesson 3 — Conditionals</h2>
        <div style="color:#a9bdc6">Use <code>if</code> to make decisions.</div>
      </div>
      <div><a class="btn" href="dashboard.php">← Dashboard</a></div>
    </div>

    <div class="card" style="margin-top:12px">
      <div style="font-weight:700">Challenge</div>
      <p style="color:#a9bdc6">Given a variable <code>n</code>, print whether it's <strong>"Even"</strong> or <strong>"Odd"</strong>.</p>
      <textarea id="code">// Example solution:\n// const n = 4;\n// if (n % 2 === 0) console.log('Even'); else console.log('Odd');\n</textarea>
      <div class="row" style="margin-top:8px">
        <button class="btn run" id="run">Run</button>
        <button class="btn" id="check">Check</button>
        <button class="btn" id="solution">Solution</button>
      </div>

      <div style="margin-top:12px">
        <div style="font-weight:700">Console</div>
        <div class="output" id="out"></div>
      </div>
    </div>
  </div>

<script>
  const out = document.getElementById('out');
  function clearOut(){ out.innerHTML=''; }
  function appendOut(t){ const d = document.createElement('div'); d.textContent = t; out.appendChild(d); }

  function run(code){
    clearOut();
    const cb = window.console;
    try {
      const cap = [];
      window.console = { log: (...a) => cap.push(a.map(x => typeof x === 'object' ? JSON.stringify(x) : String(x)).join(' ')) };
      new Function(code)();
      cap.forEach(c => appendOut(c));
      window.console = cb;
    } catch(e) { appendOut('Error: ' + e.message); window.console = cb; }
  }

  document.getElementById('run').addEventListener('click', ()=>run(document.getElementById('code').value));
  document.getElementById('solution').addEventListener('click', ()=>{ document.getElementById('code').value = "const n = 4; if (n % 2 === 0) console.log('Even'); else console.log('Odd');"; });

  document.getElementById('check').addEventListener('click', ()=>{
    clearOut();
    try {
      const cap = [];
      const cb = window.console;
      window.console = { log: (...a) => cap.push(a.map(x => typeof x === 'object' ? JSON.stringify(x) : String(x)).join(' ')) };
      new Function(document.getElementById('code').value)();
      window.console = cb;
      const ok = cap.some(s => /Even|Odd/.test(s));
      if(ok){
        appendOut('✅ Correct — conditional works.');
        fetch(window.location.href, {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: 'action=save_progress&lesson=lesson3&badge=Logic+Badge'
        }).then(r=>r.json()).then(j=>{
          if(j.ok){ appendOut('Progress saved — redirecting...'); setTimeout(()=>location.href='dashboard.php',900); }
          else appendOut('Save failed: ' + (j.msg||'unknown'));
        });
      } else {
        appendOut('❌ Try printing "Even" or "Odd" based on the number.');
      }
    } catch(e){ appendOut('Error: ' + e.message); }
  });
</script>
</body>
</html>
