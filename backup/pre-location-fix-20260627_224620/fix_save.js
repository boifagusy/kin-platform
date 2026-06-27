const fs = require('fs');
const file = 'src/screens/settings/CheckInSettingsScreen.jsx';
let content = fs.readFileSync(file, 'utf8');

const newFunc = `  const saveAndContinue = async () => {
    setSaving(true);
    setMessage(null);
    
    const token = localStorage.getItem("kin_token");
    const url = \`\${API_BASE}/checkin-settings\`;
    
    alert("DEBUG TOKEN: " + token + "\\nURL: " + url);

    try {
      const response = await fetch(url, {
        method: "POST",
        headers: { 
          "Content-Type": "application/json", 
          "Authorization": \`Bearer \${token}\`, 
          "Accept": "application/json"
        },
        body: JSON.stringify({
          checkin_time: settings.checkin_time,
          grace_minutes: parseInt(settings.grace_minutes),
          enabled: settings.enabled,
        }),
      });

      const text = await response.text();
      alert("STATUS: " + response.status + "\\nRESPONSE: " + text);

      let data;
      try { data = JSON.parse(text); } catch(e) { data = { success: false, message: text }; }

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
  };`;

content = content.replace(/  const saveAndContinue = async \(\) => \{[\s\S]*?  \};\n/g, newFunc + '\n');
fs.writeFileSync(file, content);
console.log("Fixed and saved successfully!");
