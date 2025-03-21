"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

interface Tab {
  name: string;
  href: string;
}

interface TabsProps {
  tabs: Tab[];
}

export default function Tabs({ tabs }: TabsProps) {
  const pathname = usePathname();

  return (
    <div className="flex space-x-4 border-b" >
      {tabs.map((tab) => (
        <Link
          key={tab.name}
          href={tab.href}
          className={`px-4 py-2 text-sm font-medium ${
            pathname === tab.href
              ? "border-b-2 border-blue-500 text-blue-500"
              : "text-gray-500 hover:text-gray-700"
          }`}
        >
          {tab.name}
        </Link>
      ))}
    </div>
  );
}