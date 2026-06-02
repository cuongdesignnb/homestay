import urllib.request

try:
    with urllib.request.urlopen('https://condaoislandtour.com/') as response:
        print("Status:", response.status)
        print("Headers:", response.headers)
        print("Content:", response.read().decode('utf-8')[:300])
except Exception as e:
    print("Error:", e)
