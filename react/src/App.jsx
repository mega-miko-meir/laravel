import { useState } from "react";
import reactLogo from "./assets/react.svg";
import viteLogo from "/vite.svg";
import "./App.css";
import Header from "./components/Header.jsx";
import { ways, differences } from "./data.js";
import WayToTeach from "./components/WayToTeach.jsx";
// import "tailwindcss/tailwind.css";
import "./index.css";
import Button from "./components/Button.jsx";

export default function App() {
    const [content, setContent] = useState("Press the button");

    console.log("App render");

    function handleClick(type) {
        setContent(type);
        console.log(content);
    }

    return (
        <div>
            <Header />
            <h1 className="bg-blue-500 text-white">Hello React</h1>
            <WayToTeach {...ways[0]} />
            <WayToTeach
                title={ways[1].title}
                description={ways[1].description}
            />
            <WayToTeach
                title={ways[2].title}
                description={ways[2].description}
            />
            <Button onClick={() => handleClick("way")}>Кнопка 1</Button>
            <Button onClick={() => handleClick("easy")}>Кнопка 2</Button>
            <Button onClick={() => handleClick("program")}>Кнопка 3</Button>

            <p className="my-4">{differences[content]}</p>
        </div>
    );
}
