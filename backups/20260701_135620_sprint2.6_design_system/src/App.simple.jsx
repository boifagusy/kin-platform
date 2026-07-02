import { BrowserRouter, Routes, Route } from "react-router-dom";
import TailwindTest from "./pages/TailwindTest";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<TailwindTest />} />
        <Route path="/tailwind-test" element={<TailwindTest />} />
      </Routes>
    </BrowserRouter>
  );
}
