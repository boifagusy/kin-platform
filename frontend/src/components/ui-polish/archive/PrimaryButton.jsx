function PrimaryButton({ onClick, children }) {
  return (
    <button
      onClick={onClick}
      className="w-full h-14 rounded-full bg-gradient-to-r from-primary to-primary-dark text-white font-semibold text-base shadow-lg hover:shadow-xl transition-all active:scale-98 duration-200"
    >
      {children}
    </button>
  );
}

export default PrimaryButton;
