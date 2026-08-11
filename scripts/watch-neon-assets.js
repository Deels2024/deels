const {spawn} = require('child_process');
const path = require('path');

const root = path.resolve(__dirname, '..');

const watchers = [
  ['css', 'scripts/build-neon-css.js'],
  ['js', 'scripts/build-neon-js.js'],
].map(([name, script]) => {
  const child = spawn(process.execPath, [script, '--watch'], {
    cwd: root,
    stdio: ['ignore', 'pipe', 'pipe'],
  });

  child.stdout.on('data', (data) => {
    process.stdout.write(`[${name}] ${data}`);
  });

  child.stderr.on('data', (data) => {
    process.stderr.write(`[${name}] ${data}`);
  });

  child.on('exit', (code, signal) => {
    if (signal) {
      return;
    }

    console.log(`[${name}] watcher exited with code ${code}`);
  });

  return child;
});

function stopWatchers() {
  watchers.forEach((watcher) => {
    if (!watcher.killed) {
      watcher.kill('SIGINT');
    }
  });
}

process.on('SIGINT', () => {
  stopWatchers();
  process.exit(0);
});

process.on('SIGTERM', () => {
  stopWatchers();
  process.exit(0);
});
