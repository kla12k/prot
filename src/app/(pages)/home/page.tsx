
'use client'

export default function Home() {
  return (
    <main className="min-h-screen p-8">
      <div className="max-w-7xl mx-auto">
        <h1 className="text-4xl font-bold mb-8">Gym Management Dashboard</h1>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold mb-4">Members</h2>
            <p className="text-3xl font-bold text-blue-600">150</p>
            <p className="text-gray-600">Active Members</p>
          </div>

          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold mb-4">Classes</h2>
            <p className="text-3xl font-bold text-green-600">12</p>
            <p className="text-gray-600">Active Classes</p>
          </div>

          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold mb-4">Trainers</h2>
            <p className="text-3xl font-bold text-purple-600">8</p>
            <p className="text-gray-600">Available Trainers</p>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold mb-4">Quick Actions</h2>
            <div className="space-y-4">
              <button className="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                Add New Member
              </button>
              <button className="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">
                Schedule Class
              </button>
              <button className="w-full bg-purple-500 text-white py-2 rounded hover:bg-purple-600">
                View Reports
              </button>
            </div>
          </div>

          <div className="bg-white p-6 rounded-lg shadow-md">
            <h2 className="text-xl font-semibold mb-4">Today's Schedule</h2>
            <div className="space-y-3">
              <div className="flex justify-between items-center">
                <div>
                  <p className="font-semibold">Yoga Class</p>
                  <p className="text-gray-600">9:00 AM - 10:00 AM</p>
                </div>
                <span className="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                  Active
                </span>
              </div>
              <div className="flex justify-between items-center">
                <div>
                  <p className="font-semibold">CrossFit</p>
                  <p className="text-gray-600">11:00 AM - 12:00 PM</p>
                </div>
                <span className="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                  Active
                </span>
              </div>
              <div className="flex justify-between items-center">
                <div>
                  <p className="font-semibold">Spinning</p>
                  <p className="text-gray-600">2:00 PM - 3:00 PM</p>
                </div>
                <span className="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                  Upcoming
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  )
}
