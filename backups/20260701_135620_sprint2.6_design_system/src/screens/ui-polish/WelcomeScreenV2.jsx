import { useNavigate } from "react-router-dom";
import SafetyNetworkHero from "../../components/ui-polish/SafetyNetworkHero";
import FeaturePill from "../../components/ui-polish/FeaturePill";
import PrimaryButton from "../../components/ui-polish/PrimaryButton";
import SecondaryLink from "../../components/ui-polish/SecondaryLink";

function WelcomeScreenV2() {
  const navigate = useNavigate();

  const handleGetStarted = () => {
    navigate("/login");
  };

  const handleLogIn = () => {
    navigate("/login");
  };

  return (
    <div className="min-h-screen bg-mesh overflow-hidden">
      <div className="fixed inset-0 bg-gradient-to-br from-primary/5 via-transparent to-secondary/5 pointer-events-none" />
      <main className="relative z-10 flex flex-col items-center justify-between min-h-screen px-6 py-12">
        <div className="flex-1" />
        <div className="w-full max-w-sm text-center">
          <SafetyNetworkHero />
          <h1 className="text-4xl md:text-5xl font-extrabold tracking-tight text-primary mt-6">
            Your safety network,
            <br />
            always one tap away.
          </h1>
          <p className="text-text-secondary text-base mt-3 px-2">
            Add trusted contacts, share your location during emergencies,
            and get help when it matters most.
          </p>
        </div>
        <div className="w-full max-w-sm mt-8 flex flex-wrap justify-center gap-3">
          <FeaturePill icon="group" text="Trusted Contacts" />
          <FeaturePill icon="location_on" text="Live Location" />
          <FeaturePill icon="notifications_active" text="Emergency Alerts" />
        </div>
        <p className="text-text-secondary text-sm mt-6">
          Join families using KIN to stay connected during emergencies.
        </p>
        <div className="w-full max-w-sm mt-8 space-y-3">
          <PrimaryButton onClick={handleGetStarted}>
            Build My Safety Network
          </PrimaryButton>
          <SecondaryLink onClick={handleLogIn}>
            Log In
          </SecondaryLink>
        </div>
        <div className="flex-1" />
      </main>
    </div>
  );
}

export default WelcomeScreenV2;
