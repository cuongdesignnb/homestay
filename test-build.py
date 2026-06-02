import subprocess

try:
    result = subprocess.run(["npm", "run", "build"], cwd="d:\\Homestay\\frontend", capture_output=True, text=True, shell=True)
    print("STDOUT:", result.stdout)
    print("STDERR:", result.stderr)
except Exception as e:
    print("Error:", e)
