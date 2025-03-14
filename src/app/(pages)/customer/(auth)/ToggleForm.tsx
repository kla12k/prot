// customer/auth/ToggleForm.tsx
"use client";

import { useState } from "react";

export default function ToggleForm() {
  const [isSignUp, setIsSignUp] = useState(false);

  return (
    <div className={`cont ${isSignUp ? "s--signup" : ""}`}>
      <div className="form sign-in">
        <h2 className="text-2xl text-center">Welcome</h2>
        <label className="block text-center mt-6">
          <span className="text-sm uppercase text-gray-500">Email</span>
          <input
            type="email"
            className="w-full text-center mt-2 border-b border-gray-400 focus:border-blue-500 outline-none"
          />
        </label>
        <label className="block text-center mt-6">
          <span className="text-sm uppercase text-gray-500">Password</span>
          <input
            type="password"
            className="w-full text-center mt-2 border-b border-gray-400 focus:border-blue-500 outline-none"
          />
        </label>
        <p className="text-center mt-4 text-gray-500">Forgot password?</p>
        <button className="w-full mt-8 bg-yellow-400 text-white py-2 rounded-full">Sign In</button>
      </div>

      <div className="sub-cont relative flex">
        <div className="img w-1/3 relative bg-cover" style={{ backgroundImage: "url('/path/to/ext.jpg')" }}>
          <div className={`img__text ${isSignUp ? "m--up" : "m--in"}`}>
            <h3 className="text-white text-lg">
              {isSignUp ? "Don't have an account? Please Sign up!" : "If you already have an account, just sign in."}
            </h3>
          </div>
          <div className="img__btn absolute bottom-4 left-0 right-0 mx-auto w-24 h-10 border-2 border-white text-white text-sm uppercase cursor-pointer rounded-full">
            <span onClick={() => setIsSignUp(!isSignUp)}>
              {isSignUp ? "Sign In" : "Sign Up"}
            </span>
          </div>
        </div>

        <div className="form sign-up flex-1">
          <h2 className="text-2xl text-center">Create your Account</h2>
          <label className="block text-center mt-6">
            <span className="text-sm uppercase text-gray-500">Name</span>
            <input
              type="text"
              className="w-full text-center mt-2 border-b border-gray-400 focus:border-blue-500 outline-none"
            />
          </label>
          <label className="block text-center mt-6">
            <span className="text-sm uppercase text-gray-500">Email</span>
            <input
              type="email"
              className="w-full text-center mt-2 border-b border-gray-400 focus:border-blue-500 outline-none"
            />
          </label>
          <label className="block text-center mt-6">
            <span className="text-sm uppercase text-gray-500">Password</span>
            <input
              type="password"
              className="w-full text-center mt-2 border-b border-gray-400 focus:border-blue-500 outline-none"
            />
          </label>
          <button className="w-full mt-8 bg-yellow-400 text-white py-2 rounded-full">Sign Up</button>
        </div>
      </div>
    </div>
  );
}