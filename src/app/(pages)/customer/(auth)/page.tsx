"use client";
import React, { useState } from 'react';
import './styles.css'; // Ensure your custom CSS file is linked
import { useRouter } from "next/navigation";
const AuthPage = () => {
  const [isRightPanelActive, setIsRightPanelActive] = useState(false);
  const [isLogin, setIsLogin] = useState(true);
  const router = useRouter();
  const handleSignUpClick = () => {
    setIsRightPanelActive(true);
    setIsLogin(false);
  };

  const handleSignInClick = () => {
    setIsRightPanelActive(false);
    setIsLogin(true);
  };

  const handleLoginSubmit = (e) => {
    e.preventDefault();
    router.push('/');
  };

  const handleSignUpSubmit = (e) => {
    e.preventDefault();
    // Handle sign-up logic here
    console.log('Sign-up form submitted');
  };
  return (
    <div className="min-h-screen bg-cover bg-center flex items-center justify-center" >
      <div className={`container ${isRightPanelActive ? 'right-panel-active' : ''}`} id="container" >
        {!isLogin && (
          <div className="form-container sign-up-container">
            <form onSubmit={handleSignUpSubmit}>
              <h1 className="text-4xl text-center font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-red-600 mb-6">Create Account</h1>

              <div className="social-container text-center">
                <a href="#" className="social"><i className="fab fa-facebook-f"></i></a>
                <a href="#" className="social"><i className="fab fa-google-plus-g"></i></a>
                <a href="#" className="social"><i className="fab fa-linkedin-in"></i></a>
              </div>

              <span className="text-black text-center">or use your email for registration</span>

              <div className="text-black input-lable">Full Name</div>
              <input type="text" placeholder="Name" required />

              <div className="text-black input-lable">Email</div>
              <input type="email" placeholder="Email" required />

              <div className="text-black input-lable">Phone Number</div>
              <input type="text" placeholder="phone" required />

              <div className="text-black input-lable">Password</div>
              <input type="password" placeholder="Password" required />

              <div className="text-black input-lable">Confirm Password</div>
              <input type="password" placeholder="Password" required />

              <button type="submit">Sign Up</button>
            </form>

          </div>
        )}
        {isLogin && (
          <div className="form-container sign-in-container">
            <form onSubmit={handleLoginSubmit}>
              <h1 className="text-4xl text-center font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-red-600 mb-6">Sign in</h1>
              <div className="social-container text-center">
                <a href="#" className="social"><i className="fab fa-facebook-f"></i></a>
                <a href="#" className="social"><i className="fab fa-google-plus-g"></i></a>
                <a href="#" className="social"><i className="fab fa-linkedin-in"></i></a>
              </div>
              <span className="text-black text-center">or use your email for registration</span>
              <div className="text-black input-lable">Email</div>
              <input type="email" placeholder="Email" required />
              <div className="text-black input-lable">Password</div>
              <input type="password" placeholder="Password" required />
              <a className='text-right' href="#">Forgot your password?</a>
              <button type="submit">Sign In</button>
            </form>
          </div>
        )}

        <div className="overlay-container">
          <div className="overlay">
            <div className="overlay-panel overlay-left">
              <h1 className="text-4xl text-center font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-red-600 mb-6">Welcome Back!</h1>
              <p className="text-2xl text-gray-50 font-extrabold font-sans md:font-serif mb-8">To keep connected with us please login with your personal info</p>
              <button className="ghost" onClick={handleSignInClick} id="signIn">Sign In</button>
            </div>
            <div className="overlay-panel overlay-right">
              <h1 className="text-4xl text-center font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-red-600 mb-6">Hello, Friend!</h1>
              <p className="text-2xl text-gray-50 font-extrabold  font-sans md:font-serif mb-8">Enter your personal details and start your journey with us</p>
              <button className="ghost" onClick={handleSignUpClick} id="signUp">Sign Up</button>
            </div>
          </div>
        </div>
      </div>

      {/* <footer>
        <p>
          Created with <i className="fa fa-heart"></i> by
          <a target="_blank" rel="noopener noreferrer" href="https://florin-pop.com">Florin Pop</a>
          - Read how I created this and how you can join the challenge
          <a target="_blank" rel="noopener noreferrer" href="https://www.florin-pop.com/blog/2019/03/double-slider-sign-in-up-form/">here</a>.
        </p>
      </footer> */}
    </div>
  );
}

export default AuthPage;