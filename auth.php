<?php
session_start();

$USERS_FILE = __DIR__ . '/users.json';
if(!file_exists($USERS_FILE)) {
  // ensure user created users.json before using website; but create fallback to avoid crash
  file_put_contents($USERS_FILE, json_encode(new stdClass(), JSON_PRETTY_PRINT), LOCK_EX);
}

function load_users($file){
  $raw = @file_get_contents($file);
  $obj = json_decode($raw, true);
  return is_array($obj) ? $obj : [];
}
function save_users($file, $data){
  file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

// handle POST for signup/login
$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $users = load_users($USERS_FILE);
  $action = $_POST['action'];
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if($username === '' || $password === '') {
    $errors[] = "Username & password required.";
  } else {
    $safe = preg_match('/^[A-Za-z0-9_\\-]{3,30}$/', $username);
    if(!$safe) $errors[] = "Username must be 3-30 chars: letters, numbers, _ or -";
  }

  if(empty($errors)) {
    if($action === 'signup') {
      if(isset($users[$username])) {
        $errors[] = "Username already exists.";
      } else {
        $users[$username] = [
          'password' => password_hash($password, PASSWORD_DEFAULT),
          'progress' => [],
          'badges' => []
        ];
        save_users($USERS_FILE, $users);
        $_SESSION['user'] = $username;
        header('Location: dashboard.php'); exit;
      }
    } elseif($action === 'login') {
      if(!isset($users[$username]) || !password_verify($password, $users[$username]['password'])) {
        $errors[] = "Wrong username or password.";
      } else {
        $_SESSION['user'] = $username;
        header('Location: dashboard.php'); exit;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Sign Up / Login — CodeSprout</title>
<style>
  :root{--bg:#071829;--card:#0b2834;--accent:#7cd8b3;--muted:#a7bdc5}
  body{margin:0;background:linear-gradient(180deg,#021220,#07142a);color:#eafbf4;font-family:Inter,system-ui,Arial}
  .wrap{max-width:900px;margin:40px auto;padding:18px}
  .card{background:linear-gradient(180deg,rgba(255,255,255,0.02),transparent);padding:22px;border-radius:14px}
  label{display:block;margin-top:12px;color:var(--muted)}
  input{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,0.03);background:transparent;color:inherit;margin-top:6px}
  .row{display:flex;gap:10px;margin-top:14px}
  .btn{padding:10px 12px;border-radius:10px;border:0;cursor:pointer}
  .primary{background:var(--accent);color:#04202a}
  .alt{background:transparent;border:1px solid rgba(255,255,255,0.04)}
  .errors{color:#ffb3b3;margin-top:8px}
</style>
</head>
<body>
  <div class="wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
      <div>
        <h2 style="margin:0">CodeSprout — Sign Up / Login</h2>
        <div style="color:var(--muted)">Create an account to save progress to the server.</div>
      </div>
      <div><a href="index.php" style="color:var(--muted)">← Home</a></div>
    </div>

    <div class="card">
      <?php if(!empty($errors)): ?>
        <div class="errors"><?php echo htmlspecialchars(implode(' • ', $errors)); ?></div>
      <?php endif; ?>

      <form id="authForm" method="POST" style="margin-top:12px">
        <input type="hidden" name="action" id="action" value="signup" />
        <label>Username</label>
        <input name="username" id="username" placeholder="e.g. farwa123" value="<?php echo isset($_POST['username'])?htmlspecialchars($_POST['username']):''; ?>" />
        <label>Password</label>
        <input name="password" id="password" type="password" placeholder="Choose a password" />
        <div class="row">
          <button type="submit" class="btn primary" id="primaryBtn">Create account</button>
          <button type="button" class="btn alt" id="toggleBtn">Switch to Login</button>
        </div>
      </form>

      <div style="color:var(--muted);margin-top:12px">Accounts are saved to the server in <code>users.json</code>.</div>
    </div>
  </div>

<script>
  // toggle mode
  const toggleBtn = document.getElementById('toggleBtn');
  const actionInput = document.getElementById('action');
  const primaryBtn = document.getElementById('primaryBtn');
  let mode = 'signup';
  toggleBtn.addEventListener('click', () => {
    mode = mode === 'signup' ? 'login' : 'signup';
    actionInput.value = mode;
    primaryBtn.textContent = mode === 'signup' ? 'Create account' : 'Login';
    toggleBtn.textContent = mode === 'signup' ? 'Switch to Login' : 'Switch to Sign Up';
  });

  // quick client-side simple validation
  document.getElementById('authForm').addEventListener('submit', e=>{
    const u = document.getElementById('username').value.trim();
    const p = document.getElementById('password').value;
    if(!u || !p) { e.preventDefault(); alert('Please enter username and password.'); }
  });
</script>
</body>
</html>
