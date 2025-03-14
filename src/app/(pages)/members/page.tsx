
'use client'

import { useState } from 'react'

export default function MembersPage() {
  const [members, setMembers] = useState([
    { id: 1, name: 'John Doe', membership: 'Premium' },
    { id: 2, name: 'Jane Smith', membership: 'Basic' },
    { id: 3, name: 'Mike Johnson', membership: 'Premium' },
  ])

  return (
    <div className="p-4">
      <h1 className="text-2xl font-bold mb-4">Gym Members</h1>
      <ul className="space-y-2">
        {members.map((member) => (
          <li 
            key={member.id}
            className="p-3 bg-white rounded shadow"
          >
            <p className="font-medium">{member.name}</p>
            <p className="text-gray-600">{member.membership}</p>
          </li>
        ))}
      </ul>
    </div>
  )
}

