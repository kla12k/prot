"use client";
import { useForm } from "react-hook-form";

export default function RegisterPage() {
  const { register, handleSubmit, formState: { errors } } = useForm();

  const onSubmit = (data) => {
    console.log(data);
  };

  return (
    <div className="cont s--signup">
      <form onSubmit={handleSubmit(onSubmit)} className="form sign-up">
        <h2>Create your Account</h2>
        <label>
          <span>Name</span>
          <input
            type="text"
            className="w-full text-center"
            {...register("name", { required: "Name is required" })}
          />
          {errors.name && <p className="text-red-500">{errors.name.message}</p>}
        </label>
        <label>
          <span>Email</span>
          <input
            type="email"
            className="w-full text-center"
            {...register("email", { required: "Email is required" })}
          />
          {errors.email && <p className="text-red-500">{errors.email.message}</p>}
        </label>
        <label>
          <span>Password</span>
          <input
            type="password"
            className="w-full text-center"
            {...register("password", { required: "Password is required" })}
          />
          {errors.password && <p className="text-red-500">{errors.password.message}</p>}
        </label>
        <button type="submit" className="submit">
          Sign Up
        </button>
      </form>
      {/* Rest of the code */}
    </div>
  );
}