import { useState, useRef, useEffect } from "react";
import { FaArrowLeft } from "react-icons/fa";
import { useLocation, useNavigate } from "react-router-dom";
import { loginPin } from "../../services/api";

function LoginPinScreen() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || "";

  const [pin, setPin] = useState(["", "", "", ""]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [shake, setShake] = useState(false);

  const inputRefs = [useRef(null), useRef(null), useRef(null), useRef(null)];
  const submitAttempted = useRef(false);

  // Auto-submit when all digits are filled
  useEffect(() => {
    const pinString = pin.join("");
    if (pinString.length === 4 && !loading && !submitAttempted.current) {
      submitAttempted.current = true;
      handleSubmit();
    }
  }, [pin]);

  const handleChange = (index, value) => {
    // Reset submit attempt flag when user changes input
    submitAttempted.current = false;
    setError("");
    setShake(false);

    if (value.length > 1) return;
    const newPin = [...pin];
    newPin[index] = value;
    setPin(newPin);

    // Auto-advance to next field
    if (value && index < 3) {
      inputRefs[index + 1].current?.focus();
    }
  };

  const handleKeyDown = (index, e) => {
    if (e.key === "Backspace" && !pin[index] && index > 0) {
      inputRefs[index - 1].current?.focus();
    }
  };

  const handlePaste = (e) => {
    const pasted = e.clipboardData.getData("text");
    if (!/^\d{4}$/.test(pasted)) return;

    e.preventDefault();
    const digits = pasted.split("");
    setPin(digits);
    // Auto-submit will trigger via useEffect
  };

  const handleSubmit = async () => {
    const pinString = pin.join("");
    if (pinString.length !== 4) {
      setError("Please enter your 4-digit PIN");
      return;
    }

    try {
      setLoading(true);
      setError("");
      setShake(false);

      const data = await loginPin(phone, pinString);

      if (data.success) {
        localStorage.setItem("kin_token", data.token);
        localStorage.setItem("kin_phone", phone);
        navigate("/dashboard", { state: { phone } });
      } else {
        setError(data.message || "Invalid PIN. Please try again.");
        setShake(true);
        // Clear PIN fields on error
        setPin(["", "", "", ""]);
        inputRefs[0].current?.focus();
        submitAttempted.current = false;
        // Reset shake after animation
        setTimeout(() => setShake(false), 500);
      }
    } catch (err) {
      console.error("Login error:", err);
      setError(err.message || "Unable to connect to Kin server.");
      setShake(true);
      setPin(["", "", "", ""]);
      inputRefs[0].current?.focus();
      submitAttempted.current = false;
    } finally {
      setLoading(false);
    }
  };

  const handleManualSubmit = () => {
    submitAttempted.current = true;
    handleSubmit();
  };

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <button
          onClick={() => navigate("/")}
          className="text-gray-500 hover:text-gray-700 mb-4"
        >
          <FaArrowLeft />
        </button>

        <h2 className="text-lg font-semibold text-gray-800">Enter your PIN</h2>
        <p className="text-sm text-gray-500 mb-4">{phone}</p>

        {error && (
          <div className={`bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg p-3 mb-4 ${
            shake ? "animate-shake" : ""
          }`}>
            {error}
          </div>
        )}

        <div className="flex justify-center gap-3 mb-6" onPaste={handlePaste}>
          {[0, 1, 2, 3].map((index) => (
            <input
              key={index}
              ref={inputRefs[index]}
              id={`pin-${index}`}
              type="password"
              maxLength={1}
              value={pin[index]}
              onChange={(e) => handleChange(index, e.target.value)}
              onKeyDown={(e) => handleKeyDown(index, e)}
              className={`w-14 h-14 text-center text-2xl font-bold border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent ${
                error ? "border-red-400" : ""
              }`}
              autoFocus={index === 0}
              disabled={loading}
            />
          ))}
        </div>

        <button
          onClick={handleManualSubmit}
          disabled={loading || pin.join("").length !== 4}
          className={`w-full py-3 rounded-lg font-semibold transition-colors ${
            !loading && pin.join("").length === 4
              ? "bg-green-600 text-white hover:bg-green-700"
              : "bg-gray-200 text-gray-400 cursor-not-allowed"
          }`}
        >
          {loading ? (
            <span className="flex items-center justify-center gap-2">
              <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Verifying...
            </span>
          ) : (
            "Verify PIN"
          )}
        </button>
      </div>
    </div>
  );
}

export default LoginPinScreen;
