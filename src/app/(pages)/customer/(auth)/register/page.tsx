"use client";
import { useForm } from "react-hook-form";

export default function RegisterPage() {
  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm();

  const onSubmit = (data) => {
    console.log(data);
    // Simulate an API call
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve();
      }, 2000);
    });
  };

  return (
    <div className="cont s--signup">
      <form onSubmit={handleSubmit(onSubmit)} className="form sign-up">
        <h2 className="text-3xl font-bold text-center mb-8 text-gray-800">
          Create your Account
        </h2>
        <div className="space-y-6">
          <label className="block">
            <span className="text-sm font-medium text-gray-700">Name</span>
            <input
              type="text"
              className="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
              {...register("name", { required: "Name is required" })}
            />
            {errors.name && (
              <p className="text-red-500 text-sm mt-1">{errors.name.message}</p>
            )}
          </label>
          <label className="block">
            <span className="text-sm font-medium text-gray-700">Email</span>
            <input
              type="email"
              className="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
              {...register("email", { required: "Email is required" })}
            />
            {errors.email && (
              <p className="text-red-500 text-sm mt-1">{errors.email.message}</p>
            )}
          </label>
          <label className="block">
            <span className="text-sm font-medium text-gray-700">Password</span>
            <input
              type="password"
              className="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
              {...register("password", { required: "Password is required" })}
            />
            {errors.password && (
              <p className="text-red-500 text-sm mt-1">{errors.password.message}</p>
            )}
          </label>
        </div>
        <button
          type="submit"
          className="w-full mt-8 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:bg-blue-400"
          disabled={isSubmitting}
        >
          {isSubmitting ? "Signing Up..." : "Sign Up"}
        </button>
      </form>

      {/* Toggle between Login and Register */}
      <div className="sub-cont">
        <div className="img">
          <div className="img__text m--up">
            <h3 className="text-xl font-semibold text-white">
              Don't have an account? Please Sign up!
            </h3>
          </div>
          <div className="img__text m--in">
            <h3 className="text-xl font-semibold text-white">
              If you already have an account, just sign in.
            </h3>
          </div>
          <div
            className="img__btn"
            onClick={() => document.querySelector('.cont').classList.toggle('s--signup')}
          >
            <span className="m--up">Sign Up</span>
            <span className="m--in">Sign In</span>
          </div>
        </div>
      </div>
    </div>
  );
}