import { execSync } from 'child_process';

try {
    const stdout = execSync('npm run build', { cwd: 'd:/Homestay/frontend', encoding: 'utf-8', stdio: 'pipe' });
    console.log("BUILD SUCCESS:");
    console.log(stdout);
} catch (error) {
    console.error("BUILD FAIL:");
    console.error(error.stdout);
    console.error(error.stderr);
}
