import { Server } from 'node:net';
// Playground has no bind-host option; keep this development server on loopback.
const listen = Server.prototype.listen;
Server.prototype.listen = function (...args) {
  if (typeof args[0] === 'number') args.splice(1, 0, '127.0.0.1');
  return listen.apply(this, args);
};
import { existsSync, mkdirSync } from 'node:fs';
import { runCLI } from '@wp-playground/cli';
mkdirSync('runtime', { recursive: true });
const installed = existsSync('runtime/wp-config.php');
await runCLI({
  command: 'server', port: 9400, php: '8.3', wp: '7.1.3', workers: 6,
  wordpressInstallMode: installed ? 'do-not-attempt-installing' : 'download-and-install',
  'mount-before-install': [
    { hostPath: './runtime', vfsPath: '/wordpress' },
    { hostPath: './plugins/rssa-telegram', vfsPath: '/wordpress/wp-content/plugins/rssa-telegram' },
    { hostPath: './theme', vfsPath: '/wordpress/wp-content/themes/rssa' },
  ],
  ...(installed ? {} : { blueprint: './blueprint.json' }),
});
