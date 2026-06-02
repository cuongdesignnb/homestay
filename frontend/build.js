import { build } from 'vite';

async function runBuild() {
  try {
    await build({
      root: 'd:/Homestay/frontend',
    });
    console.log("Build successful!");
  } catch (e) {
    console.error("Build failed:", e);
  }
}

runBuild();
