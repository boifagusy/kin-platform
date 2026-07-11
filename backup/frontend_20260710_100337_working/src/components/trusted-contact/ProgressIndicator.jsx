import React from 'react';

const ProgressIndicator = ({ currentStep = 4, totalSteps = 6 }) => {
  const steps = Array.from({ length: totalSteps }, (_, i) => i + 1);
  
  return (
    <div style={{ display: "flex", justifyContent: "center", gap: "10px", marginBottom: "36px" }}>
      {steps.map((step) => (
        <div
          key={step}
          style={{
            width: "12px",
            height: "12px",
            borderRadius: "50%",
            background: step < currentStep ? "#1A5632" : step === currentStep ? "#D4A017" : "#D1D5DB",
          }}
        />
      ))}
    </div>
  );
};

export default ProgressIndicator;
