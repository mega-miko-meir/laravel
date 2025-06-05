import React, { useState, useEffect } from "react";

const Header = () => {
    // var time = new Date().toLocaleTimeString();
    // const [now, setTime] = useState(new Date());

    // setInterval(() => setTime(new Date()), 1000);

    return (
        <header>
            <h3>This is my react project</h3>
            <span>The time is: {now.toLocaleTimeString()}</span>
        </header>
    );
};

export default Header;
