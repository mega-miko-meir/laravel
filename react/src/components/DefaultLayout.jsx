import { Outlet, Link } from "react-router-dom";
import { useStateContext } from "../contexts/ContextProvider";
import { Navigate } from "react-router-dom";
import "../index.css";

export default function DefaultLayout() {
    const { user, token } = useStateContext();
    if (!token) {
        return <Navigate to="/login" />;
    }
    return (
        <div className="flex h-screen bg-gray-100">
            <aside className="w-64 bg-blue-700 text-white shadow-md p-6">
                <nav className="flex flex-col space-y-4">
                    <Link
                        to="/users"
                        className="hover:bg-blue-600 px-3 py-2 rounded transition"
                    >
                        Users
                    </Link>
                    <Link
                        to="/dashboard"
                        className="hover:bg-blue-600 px-3 py-2 rounded transition"
                    >
                        Dashboard
                    </Link>
                </nav>
            </aside>
            <div className="flex-1 flex flex-col">
                <header className="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow">
                    <div className="text-xl font-bold">Dashboard</div>
                    <div className="text-sm">Welcome, User!</div>
                </header>
                <main className="flex-1 overflow-y-auto p-6">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
