import { useState } from "react";
import { FaShieldAlt, FaLock } from "react-icons/fa";
import { useNavigate } from "react-router-dom";

import NumberRegisteredPopup from "../../components/NumberRegisteredPopup";
import NumberNotRegisteredPopup from "../../components/NumberNotRegisteredPopup";
import { confirmPhone } from "../../services/api";

function PhoneEntryScreen() {
  const [phone, setPhone] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [maskedPhone, setMaskedPhone] = useState("");
  const [showRegisteredPopup, setShowRegisteredPopup] = useState(false);
  const [showNotRegisteredPopup, setShowNotRegisteredPopup] = useState(false);

  const navigate = useNavigate();

  const isValid = phone.length === 10;

  async function handleContinue() {
    try {
      setLoading(true);
      setError("");

      const data = await confirmPhone(`+234${phone}`);

      setMaskedPhone(data.masked_phone);

      if (data.exists) {
        setShowRegisteredPopup(true);
      } else {
        setShowNotRegisteredPopup(true);
      }
    } catch (err) {
      console.error("Phone confirmation error:", err);
      setError(err.message || "Unable to connect to Kin server.");
    } finally {
      setLoading(false);
    }
  }

  const handleRegisteredPopupClose = () => {
    setShowRegisteredPopup(false);
    navigate("/login-pin", { state: { phone: `+234${phone}` } });
  };

  const handleNotRegisteredPopupClose = () => {
    setShowNotRegisteredPopup(false);
    navigate("/create-pin", { state: { phone: `+234${phone}` } });
  };

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div className="flex items-center justify-between mb-6">
          <div className="flex items-center gap-2">
            <FaShieldAlt className="text-green-600 text-2xl" />
            <h1 className="text-xl font-bold text-gray-800">KIN</h1>
          </div>
          <span className="text-xs text-gray-400">Personal Safety Network</span>
        </div>

        <h2 className="text-lg font-semibold text-gray-800 mb-1">Enter your phone number</h2>
        <p className="text-sm text-gray-500 mb-4">Let's securely verify your phone number.</p>

        {error && (
          <div className="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg p-3 mb-4">
            {error}
          </div>
        )}

        <div className="mb-4">
          <label className="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
          <div className="flex">
            <span className="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
              +234
            </span>
            <input
              type="tel"
              value={phone}
              onChange={(e) => setPhone(e.target.value.replace(/\D/g, ""))}
              maxLength={10}
              placeholder="808 644 8522"
              className="flex-1 rounded-r-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
          </div>
        </div>

        <button
          onClick={handleContinue}
          disabled={!isValid || loading}
          className={`w-full py-3 rounded-lg font-semibold transition-colors ${
            isValid && !loading
              ? "bg-green-600 text-white hover:bg-green-700"
              : "bg-gray-200 text-gray-400 cursor-not-allowed"
          }`}
        >
          {loading ? "Checking..." : "Continue →"}
        </button>

        <div className="mt-4 flex items-center justify-center gap-6 text-xs text-gray-400">
          <span className="flex items-center gap-1">
            <FaLock className="text-xs" /> Encryption
          </span>
          <span>Protected with end-to-end encryption</span>
        </div>

        <p className="text-xs text-gray-400 text-center mt-3">
          Your location is never shared without your permission.
        </p>
      </div>

      <NumberRegisteredPopup
        isOpen={showRegisteredPopup}
        onClose={handleRegisteredPopupClose}
        maskedPhone={maskedPhone}
      />

      <NumberNotRegisteredPopup
        isOpen={showNotRegisteredPopup}
        onClose={handleNotRegisteredPopupClose}
        maskedPhone={maskedPhone}
      />
    </div>
  );
}

export default PhoneEntryScreen;
