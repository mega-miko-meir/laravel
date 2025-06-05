import "../App.css";

export default function Button({ children, onClick }) {
    // console.log("Button component rendered");

    return (
        <button
            onClick={onClick}
            onDoubleClick={() => console.log("Button double clicked!")}
            className="bg-gray-500 text-white py-2 px-4 rounded mx-2"
        >
            {children}
        </button>
    );
}
