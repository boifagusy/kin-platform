import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { FaArrowLeft, FaMapMarkerAlt, FaPhone, FaUserCircle, FaShieldAlt } from "react-icons/fa";

function MapScreen() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");
  
  const [contacts, setContacts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [userLocation, setUserLocation] = useState(null);
  
  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }
    
    // Get user's current location
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          setUserLocation({
            lat: pos.coords.latitude,
            lng: pos.coords.longitude
          });
        },
        (err) => {
          setUserLocation({ lat: 6.5244, lng: 3.3792 });
        }
      );
    }
    
    fetchTrustedContacts();
  }, [phone]);
  
  const fetchTrustedContacts = async () => {
    try {
      const response = await fetch(`${API_BASE}/trusted-contacts?phone=${encodeURIComponent(phone)}`);
      const data = await response.json();
      if (data.success) {
        setContacts(data.data.contacts || []);
      }
    } catch (error) {
      console.error("Error fetching contacts:", error);
    } finally {
      setLoading(false);
    }
  };
  
  const openInMaps = (lat, lng, name) => {
    const targetLat = lat || userLocation?.lat || 6.5244;
    const targetLng = lng || userLocation?.lng || 3.3792;
    window.open(`https://www.google.com/maps?q=${targetLat},${targetLng}&query=${encodeURIComponent(name || 'Contact')}`, '_blank');
  };
  
  const callContact = (phoneNumber) => {
    window.open(`tel:${phoneNumber}`, '_blank');
  };
  
  if (loading) {
    return (
      <div style={{ minHeight: "100vh", background: "#F0F7F2", display: "flex", alignItems: "center", justifyContent: "center" }}>
        <div style={{ textAlign: "center" }}>
          <div style={{ width: 30, height: 30, border: "3px solid #1A5632", borderTop: "3px solid transparent", borderRadius: "50%", animation: "spin 1s linear infinite", margin: "0 auto 10px" }}></div>
          <p style={{ color: "#1A5632", marginTop: 10 }}>Loading safety network...</p>
        </div>
      </div>
    );
  }
  
  return (
    <div style={{ minHeight: "100vh", background: "#F8F9FA", paddingBottom: 80 }}>
      {/* Header */}
      <div style={{ background: "white", padding: "16px 20px", borderBottom: "1px solid #e5e7eb", position: "sticky", top: 0, zIndex: 10 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 16 }}>
          <button onClick={() => navigate(-1)} style={{ background: "none", border: "none", cursor: "pointer" }}>
            <FaArrowLeft style={{ fontSize: 20, color: "#1A5632" }} />
          </button>
          <h1 style={{ fontSize: 20, fontWeight: "bold", color: "#1A5632", margin: 0 }}>Safety Network</h1>
        </div>
      </div>
      
      <div style={{ padding: "16px 20px", maxWidth: 500, margin: "0 auto" }}>
        
        {/* My Location Card */}
        <div style={{ background: "white", borderRadius: 20, padding: 16, marginBottom: 20, boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 12 }}>
            <div style={{ width: 48, height: 48, borderRadius: 24, background: "#e8f5e9", display: "flex", alignItems: "center", justifyContent: "center" }}>
              <FaShieldAlt style={{ fontSize: 24, color: "#1A5632" }} />
            </div>
            <div>
              <h3 style={{ fontWeight: "bold", color: "#1A5632", margin: 0 }}>My Location</h3>
              <p style={{ fontSize: 12, color: "#6b7280", marginTop: 4 }}>
                {userLocation ? `${userLocation.lat.toFixed(4)}, ${userLocation.lng.toFixed(4)}` : "Getting location..."}
              </p>
            </div>
          </div>
          <button
            onClick={() => openInMaps(userLocation?.lat, userLocation?.lng, "My Location")}
            style={{
              width: "100%",
              padding: "12px",
              background: "#1A5632",
              color: "white",
              border: "none",
              borderRadius: 12,
              fontSize: 14,
              fontWeight: "bold",
              cursor: "pointer",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              gap: 8,
            }}
          >
            <FaMapMarkerAlt />
            View My Location on Map
          </button>
        </div>
        
        {/* Trusted Contacts List */}
        <h3 style={{ fontSize: 14, fontWeight: "bold", color: "#6B7280", marginBottom: 12, letterSpacing: 0.5 }}>TRUSTED CONTACTS</h3>
        
        {contacts.length === 0 ? (
          <div style={{ background: "white", borderRadius: 20, padding: 40, textAlign: "center", boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
            <FaUserCircle style={{ fontSize: 48, color: "#D1D5DB", marginBottom: 12 }} />
            <p style={{ color: "#9CA3AF", marginBottom: 8 }}>No trusted contacts yet</p>
            <p style={{ fontSize: 12, color: "#9CA3AF", marginBottom: 16 }}>Add someone you trust</p>
            <button 
              onClick={() => navigate("/network")}
              style={{ padding: "10px 20px", background: "#1A5632", color: "white", border: "none", borderRadius: 10, fontSize: 13, cursor: "pointer" }}
            >
              + Add Contact
            </button>
          </div>
        ) : (
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {contacts.map((contact) => (
              <div key={contact.id} style={{ background: "white", borderRadius: 20, padding: 16, boxShadow: "0 1px 3px rgba(0,0,0,0.1)", borderLeft: `4px solid #1A5632` }}>
                <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 12 }}>
                  <div style={{ width: 48, height: 48, borderRadius: 24, background: "#e8f5e9", display: "flex", alignItems: "center", justifyContent: "center" }}>
                    <FaUserCircle style={{ fontSize: 24, color: "#1A5632" }} />
                  </div>
                  <div>
                    <h3 style={{ fontWeight: "bold", color: "#1A5632", margin: 0 }}>{contact.name}</h3>
                    <p style={{ fontSize: 12, color: "#6b7280", marginTop: 2 }}>{contact.phone}</p>
                    {contact.verified ? (
                      <span style={{ fontSize: 10, color: "#22c55e" }}>✓ Verified</span>
                    ) : (
                      <span style={{ fontSize: 10, color: "#eab308" }}>Pending</span>
                    )}
                  </div>
                </div>
                <div style={{ display: "flex", gap: 10 }}>
                  <button
                    onClick={() => callContact(contact.phone)}
                    style={{
                      flex: 1,
                      padding: "10px",
                      background: "#1A5632",
                      color: "white",
                      border: "none",
                      borderRadius: 12,
                      fontSize: 13,
                      fontWeight: "bold",
                      cursor: "pointer",
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      gap: 6,
                    }}
                  >
                    <FaPhone size={12} />
                    Call
                  </button>
                  <button
                    onClick={() => openInMaps(null, null, contact.name)}
                    style={{
                      flex: 1,
                      padding: "10px",
                      background: "#D4A017",
                      color: "white",
                      border: "none",
                      borderRadius: 12,
                      fontSize: 13,
                      fontWeight: "bold",
                      cursor: "pointer",
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      gap: 6,
                    }}
                  >
                    <FaMapMarkerAlt size={12} />
                    View on Map
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
        
        {/* Add Contact Button - Only if under limit (1 contact max) */}
        {contacts.length < 1 && (
          <button
            onClick={() => navigate("/network")}
            style={{
              width: "100%",
              padding: "14px",
              marginTop: 16,
              background: "white",
              border: "2px dashed #1A5632",
              borderRadius: 16,
              color: "#1A5632",
              fontSize: 14,
              fontWeight: "bold",
              cursor: "pointer",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              gap: 8,
            }}
          >
            + Add Trusted Contact
          </button>
        )}
        
        {/* Limit Message */}
        {contacts.length >= 1 && (
          <p style={{ textAlign: "center", fontSize: 11, color: "#9ca3af", marginTop: 16 }}>
            You have reached your trusted contact limit
          </p>
        )}
      </div>
      
      <style>{`
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}

export default MapScreen;
