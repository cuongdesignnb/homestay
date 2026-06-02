import urllib.request

req = urllib.request.Request(
    'https://condaoislandtour.com/', 
    headers={'User-Agent': 'Zalobot'}
)

try:
    with urllib.request.urlopen(req) as response:
        print("Status:", response.status)
        print("Content:", response.read().decode('utf-8')[:500])
except Exception as e:
    print("Error:", e)
