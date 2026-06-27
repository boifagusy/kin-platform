import re
import sys

file_path = "src/screens/settings/CheckInSettingsScreen.jsx"
with open(file_path, 'r') as f:
    content = f.read()

new_func = """  const saveAndContinue = async () => {
    setSaving(true);
    setMessage(null);
    
    const token = localStorage.getItem("kin_token");
    const url = `${API_BASE}/checkin-settings`;
    
    alert("DEBUG TOKEN: " + token + "\\nURL: " + url);

    try {
      const response = await fetch(url, {
        method: "POST",
        headers: { 
          "Content-Type": "application/json", 
          "Authorization": `Bearer ${token}`, 
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({
          checkin_time: settings.checkin_time,
          grace_minutes: parseInt(settings.grace_minutes),
          enabled: settings.enabled,
        }),
      });

      const text = await response.text();
      alert("STATUS: " + response.status + "\\nRESPONSE: " + text);

      const data = JSON.parse(text);

      if (data.success) {
        navigate("/settings/duress-pin", { state: { phone: phone } });
      } else {
        setMessage({ type: "error", text: data.message || "Failed to save settings" });
        setSaving(false);
      }
    } catch (error) {
      console.error("Save error:", error);
      setMessage({ type: "error", text: "Network error: " + error.message });
      setSaving(false);
    }
  };"""

pattern = r'  const saveAndContinue = async \(\) => \{[\s\S]*?  \};'
if not re.search(pattern, content):
    print("ERROR: Could not find saveAndContinue function!")
    sys.exit(1)

content = re.sub(pattern, new_func, content)

with open(file_path, 'w') as f:
    f.write(content)
    
print("Successfully replaced save function with diagnostic version!")
