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

// handle AJAX save
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
  echo json_encode(['ok'=>true]);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Lesson 1 — Hello Console</title>
<style>
  :root{--bg:#06122b;--card:#071427;--accent:#8ef3c8;--muted:#a9bac6}
  body{margin:0;background:linear-gradient(180deg,#021025,#051424);color:#e8fbf0;font-family:Inter,system-ui}
  .wrap{max-width:1000px;margin:28px auto;padding:18px}
  .head{display:flex;justify-content:space-between;align-items:center}
  .card{background:linear-gradient(180deg,rgba(255,255,255,0.02),transparent);padding:16px;border-radius:12px}
  .editor{display:grid;grid-template-columns:1fr 360px;gap:12px;margin-top:12px}
  textarea{width:100%;height:260px;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.04);background:transparent;color:inherit;font-family:monospace}
  .output{height:260px;overflow:auto;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.03);background:#02131a}
  .row{display:flex;gap:8px;margin-top:10px}
  .btn{padding:10px 12px;border-radius:10px;border:0;cursor:pointer}
  .run{background:var(--accent);color:#032226}
  .hint{color:var(--muted);font-size:13px;margin-top:10px}
</style>
</head>
<body>
  <div class="wrap">
    <div class="head">
      <div>
        <h2>Lesson 1 — Hello Console</h2>
        <div style="color:var(--muted)">Use <code>console.log</code> to print text to the console.</div>
      </div>
      <div>
        <a href="dashboard.php" class="btn">← Dashboard</a>
      </div>
    </div>

    <div class="card">
      <div class="editor">
        <div>
          <div style="font-weight:700">Challenge</div>
          <p style="color:var(--muted)">Write code that prints <code>Hello, CodeSprout!</code> to the console.</p>

          <textarea id="code">// Try: console.log('Hello, CodeSprout!');</textarea>

          <div class="row">
            <button class="btn run" id="run">Run</button>
            <button class="btn" id="check">Check Answer</button>
            <button class="btn" id="solution">Show Solution</button>
          </div>

          <div class="hint"><strong>Hint:</strong> Use <code>console.log()</code> with a string.</div>
        </div>

        <aside>
          <div style="font-weight:700">Console Output</div>
          <div class="output" id="out"></div>

          <div style="margin-top:12px">
            <div style="font-weight:700">Progress</div>
            <div style="color:var(--muted);margin-top:6px">Complete this lesson to earn <strong>Beginner Badge</strong>.</div>
          </div>
        </aside>
      </div>
    </div>
  </div>

<script>
  // capture console.log from user code safely (simple)
  const out = document.getElementById('out');
  function clearOut(){ out.innerHTML = ''; }
  function appendOut(txt){ const p = document.createElement('div'); p.textContent = txt; out.appendChild(p); }

  function runUserCode(code) {
    clearOut();
    const consoleBackup = window.console;
    try {
      const captured = [];
      window.console = { log: (...args) => { captured.push(args.map(a => typeof a === 'object' ? JSON.stringify(a) : String(a)).join(' ')); } };
      new Function(code)();
      captured.forEach(c => appendOut(c));
    } catch(e) {
      appendOut('Error: ' + e.message);
    } finally {
      window.console = consoleBackup;
    }
  }

  document.getElementById('run').addEventListener('click', () => {
    const code = document.getElementById('code').value;
    runUserCode(code);
  });

  document.getElementById('solution').addEventListener('click', () => {
    document.getElementById('code').value = "console.log('Hello, CodeSprout!');";
  });

  document.getElementById('check').addEventListener('click', () => {
    const code = document.getElementById('code').value;
    clearOut();
    try {
      const captured = [];
      const cb = window.console;
      window.console = { log: (...args) => { captured.push(args.map(a => typeof a === 'object' ? JSON.stringify(a) : String(a)).join(' ')); } };
      new Function(code)();
      window.console = cb;

      if (captured.includes('Hello, CodeSprout!')) {
        appendOut('✅ Correct! You printed the message.');
        // save progress via AJAX POST to this PHP file
        fetch(window.location.href, {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body: 'action=save_progress&lesson=lesson1&badge=Beginner+Badge'
        }).then(r => r.json()).then(j => {
          if(j.ok){
            appendOut('Progress saved to your account. Redirecting to dashboard...');
            setTimeout(()=>{ window.location.href = 'dashboard.php'; }, 1000);
          } else {
            appendOut('Saved failed: ' + (j.msg||'unknown'));
          }
        }).catch(e=>appendOut('Network error: ' + e.message));
      } else {
        appendOut('❌ Not yet — did you print exactly "Hello, CodeSprout!"?');
      }
    } catch(e) {
      appendOut('Error: ' + e.message);
    }
  });
</script>
</body>
</html>
