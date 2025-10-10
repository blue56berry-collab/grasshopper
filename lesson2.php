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
<title>Lesson 2 — Variables & Math</title>
<style>
  :root{--bg:#081224;--card:#071427;--accent:#8de3b8;--muted:#a7bcc7}
  body{margin:0;background:linear-gradient(180deg,#02121f,#071426);color:#eafcf4;font-family:Inter,system-ui}
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
        <h2>Lesson 2 — Variables & Math</h2>
        <div style="color:#a7bcc7">Store values and do math operations</div>
      </div>
      <div><a class="btn" href="dashboard.php">← Dashboard</a></div>
    </div>

    <div class="card" style="margin-top:12px">
      <div style="font-weight:700">Challenge</div>
      <p style="color:#a7bcc7">Create two variables <code>a = 7</code> and <code>b = 5</code>. Print the sum and product.</p>
      <textarea id="code">// Example:\n// const a = 7; const b = 5; console.log('sum:', a+b); console.log('product:', a*b);\n</textarea>
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
  function clearOut(){ out.innerHTML = ''; }
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
  document.getElementById('solution').addEventListener('click', ()=>{ document.getElementById('code').value = "const a = 7; const b = 5; console.log('sum:', a + b); console.log('product:', a * b);"; });

  document.getElementById('check').addEventListener('click', ()=>{
    clearOut();
    try {
      const cap = [];
      const cb = window.console;
      window.console = { log: (...a) => cap.push(a.map(x => typeof x === 'object' ? JSON.stringify(x) : String(x)).join(' ')) };
      new Function(document.getElementById('code').value)();
      window.console = cb;
      const hasSum = cap.some(s => s.includes('12') || s.includes('sum: 12') || s.includes('sum:12'));
      const hasProd = cap.some(s => s.includes('35') || s.includes('product: 35') || s.includes('product:35'));
      if(hasSum && hasProd){
        appendOut('✅ Correct! Sum and product found.');
        fetch(window.location.href, {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: 'action=save_progress&lesson=lesson2&badge=Math+Badge'
        }).then(r=>r.json()).then(j=>{
          if(j.ok){ appendOut('Progress saved — redirecting...'); setTimeout(()=>location.href='dashboard.php',900); }
          else appendOut('Save failed: ' + (j.msg||'unknown'));
        });
      } else {
        appendOut('❌ Not quite — check your values and logs.');
      }
    } catch(e) { appendOut('Error: ' + e.message); }
  });
</script>
</body>
</html>
