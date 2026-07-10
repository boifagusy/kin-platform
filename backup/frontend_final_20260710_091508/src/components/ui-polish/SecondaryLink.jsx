function SecondaryLink({ onClick, children }) {
  return (
    <button
      onClick={onClick}
      className="w-full py-3 text-text-secondary font-medium hover:text-primary transition-colors active:opacity-70 text-center"
    >
      {children}
    </button>
  );
}

export default SecondaryLink;
