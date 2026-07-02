export default function TailwindTest() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-green-500 to-blue-500 flex items-center justify-center">
      <div className="bg-white p-8 rounded-2xl shadow-2xl">
        <h1 className="text-3xl font-bold text-gray-800">Tailwind Test</h1>
        <p className="text-gray-600 mt-2">If you see this with colors, Tailwind is working!</p>
        <div className="mt-4 flex gap-2">
          <div className="w-10 h-10 bg-red-500 rounded-full"></div>
          <div className="w-10 h-10 bg-green-500 rounded-full"></div>
          <div className="w-10 h-10 bg-blue-500 rounded-full"></div>
        </div>
      </div>
    </div>
  )
}
