
'use client';

export default function Home() {
  return (
    <main className="min-h-screen bg-gradient-to-b from-gray-900 to-gray-800 text-white">
      <div className="container mx-auto px-4 py-16">
        <div className="text-center">
          <h1 className="text-5xl font-bold mb-6">Welcome to FitFlex Gym</h1>
          <p className="text-xl mb-8">Your journey to fitness begins here</p>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
            <div className="bg-gray-800 p-6 rounded-lg shadow-lg hover:transform hover:scale-105 transition-all">
              <h2 className="text-2xl font-semibold mb-4">Expert Trainers</h2>
              <p>Get personalized guidance from our certified fitness professionals</p>
            </div>
            
            <div className="bg-gray-800 p-6 rounded-lg shadow-lg hover:transform hover:scale-105 transition-all">
              <h2 className="text-2xl font-semibold mb-4">Modern Equipment</h2>
              <p>Access to state-of-the-art fitness equipment and facilities</p>
            </div>
            
            <div className="bg-gray-800 p-6 rounded-lg shadow-lg hover:transform hover:scale-105 transition-all">
              <h2 className="text-2xl font-semibold mb-4">Flexible Plans</h2>
              <p>Choose from various membership options that suit your needs</p>
            </div>
          </div>

          <button className="mt-12 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full transition-colors">
            Join Now
          </button>
        </div>
      </div>
    </main>
  );
}
