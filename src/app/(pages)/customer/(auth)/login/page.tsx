"use client";

export default function LoginPage() {
  return (
    <div className="cont" >
      <div className="form ">
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
      <div className="sub-cont">
        <div className="img">
          <div className="img__text m--up">
            <h3>Don't have an account? Please Sign up!</h3>
          </div>
          <div className="img__text m--in">
            <h3>If you already have an account, just sign in.</h3>
          </div>
          <div className="img__btn" onClick={() => document.querySelector('.cont').classList.toggle('s--signup')}>
            <span className="m--up">Sign Up</span>
            <span className="m--in">Sign In</span>
          </div>
        </div>
      </div>
    </div>
  );
}