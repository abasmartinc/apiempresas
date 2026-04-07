import requests
import time

def geocode_address(address_full: str):
    """
    Test version of geocode_address (Nominatim).
    """
    url = "https://nominatim.openstreetmap.org/search"
    params = {
        "q": address_full + ", Spain",
        "format": "json",
        "limit": 1,
        "addressdetails": 1
    }
    headers = {
        "User-Agent": "ApiEmpresas-BormeBot/1.0 (papelo.amh@gmail.com)"
    }

    try:
        print(f"[*] Geocodificando: {address_full}")
        resp = requests.get(url, params=params, headers=headers, timeout=10)
        if resp.status_code == 200:
            data = resp.json()
            if data:
                return float(data[0]["lat"]), float(data[0]["lon"])
            else:
                print("[!] No results found.")
        else:
            print(f"[!] HTTP Error: {resp.status_code}")
    except Exception as e:
        print(f"[!] Error: {e}")
    
    return None, None

if __name__ == "__main__":
    # Test with a real address from our samples
    # C/ AZORIN 34, JAVEA
    lat, lng = geocode_address("C/ AZORIN 34, JAVEA")
    print(f"Result: {lat}, {lng}")
    
    # Test with another one
    time.sleep(1.1)
    lat, lng = geocode_address("AVDA DEL CALVARIO 25, ALBATERA")
    print(f"Result: {lat}, {lng}")
