"use client";

import Link from "next/link";

export default function Navbar() {
  return (
    <header className="shadow-sm sticky top-0 z-50 bg-white/80 backdrop-blur-md dark:bg-gray-900/80">
      <nav className="container mx-auto px-4 flex h-14 items-center justify-between">
        <Link href="/" className="text-lg font-semibold">AuthLend</Link>
        <div />
      </nav>
    </header>
  );
}
