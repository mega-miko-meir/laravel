import { Link } from "react-router-dom";
import { useRef } from "react";
import axiosClient from "../axios-client";
import { useStateContext } from "../contexts/ContextProvider";

function Login() {
    const loginname = useRef();
    const loginpassword = useRef();

    const { setUser, setToken } = useStateContext();

    const onsubmit = (e) => {
        e.preventDefault();
        const data = {
            loginname: loginname.current.value,
            loginpassword: loginpassword.current.value,
        };
        console.log(data);

        axiosClient
            .post("/login", data)
            .then((data) => {
                setUser(data.user);
                setToken(data.token);
                console.log(data);
            })
            .catch((err) => {
                const response = err.response;
                if (response && response.status === 422) {
                    console.log(response.data.errors);
                }
            });
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-100">
            <div className="w-full max-w-md bg-white p-8 rounded shadow">
                <h2 className="text-2xl font-bold text-center text-blue-600 mb-6">
                    Login to your account
                </h2>
                <form onSubmit={onsubmit} method="POST" className="space-y-6">
                    <div>
                        <label
                            htmlFor="email"
                            className="block text-sm font-medium text-gray-700"
                        >
                            Email
                        </label>
                        <input
                            ref={loginname}
                            type="email"
                            id="email"
                            name="loginname"
                            required
                            className="mt-1 w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <label
                            htmlFor="password"
                            className="block text-sm font-medium text-gray-700"
                        >
                            Password
                        </label>
                        <input
                            ref={loginpassword}
                            type="password"
                            id="password"
                            name="loginpassword"
                            required
                            className="mt-1 w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <button
                            type="submit"
                            className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition"
                        >
                            Login
                        </button>
                    </div>
                </form>
                <p className="mt-4 text-center text-sm text-gray-600">
                    Forgot your password?{" "}
                    <a href="#" className="text-blue-600 hover:underline">
                        Reset it
                    </a>
                </p>
            </div>
        </div>
    );
}

export default Login;

// import { useState } from "react";

// function Login() {
//     const [isLogin, setIsLogin] = useState(true);
//     return (
//         <div className="min-h-screen flex items-center justify-center bg-gray-100">
//             <div className="w-full max-w-md bg-white p-8 rounded shadow">
//                 <h2 className="text-2xl font-bold text-center text-blue-600 mb-6">
//                     {isLogin ? "Login to your account" : "Create an account"}
//                 </h2>

//                 <form action="#" method="POST" className="space-y-6">
//                     {!isLogin && (
//                         <div>
//                             <label
//                                 htmlFor="fullName"
//                                 className="block text-sm font-medium text-gray-700"
//                             >
//                                 Full Name
//                             </label>
//                             <input
//                                 type="text"
//                                 id="fullName"
//                                 name="fullName"
//                                 required
//                                 className="mt-1 w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
//                             />
//                         </div>
//                     )}

//                     <div>
//                         <label
//                             htmlFor="email"
//                             className="block text-sm font-medium text-gray-700"
//                         >
//                             Email
//                         </label>
//                         <input
//                             type="email"
//                             id="email"
//                             name="email"
//                             required
//                             className="mt-1 w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
//                         />
//                     </div>

//                     <div>
//                         <label
//                             htmlFor="password"
//                             className="block text-sm font-medium text-gray-700"
//                         >
//                             Password
//                         </label>
//                         <input
//                             type="password"
//                             id="password"
//                             name="password"
//                             required
//                             className="mt-1 w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
//                         />
//                     </div>

//                     {!isLogin && (
//                         <div>
//                             <label
//                                 htmlFor="password_confirmation"
//                                 className="block text-sm font-medium text-gray-700"
//                             >
//                                 Confirm Password
//                             </label>
//                             <input
//                                 type="password"
//                                 id="password_confirmation"
//                                 name="password_confirmation"
//                                 required
//                                 className="mt-1 w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
//                             />
//                         </div>
//                     )}

//                     <div>
//                         <button
//                             type="submit"
//                             className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition"
//                         >
//                             {isLogin ? "Login" : "Register"}
//                         </button>
//                     </div>
//                 </form>

//                 <p className="mt-4 text-center text-sm text-gray-600">
//                     {isLogin
//                         ? "Don't have an account?"
//                         : "Already have an account?"}{" "}
//                     <button
//                         onClick={() => setIsLogin(!isLogin)}
//                         className="text-blue-600 hover:underline font-medium"
//                     >
//                         {isLogin ? "Register" : "Login"}
//                     </button>
//                 </p>
//             </div>
//         </div>
//     );
// }

// export default Login;
