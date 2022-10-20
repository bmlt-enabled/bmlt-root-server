import React from "react";
import RootServerApi from "../RootServerApi";
import {useNavigate} from "react-router-dom";

export const Login = ({setIsLoggedIn}) => {
  const navigate = useNavigate();
  const [username, setUsername] = React.useState("");
  const [password, setPassword] = React.useState("");

  const handleUsernameChange = (e) => {
    setUsername(e.target.value);
  }

  const handlePasswordChange = (e) => {
    setPassword(e.target.value);
  }

  const handleSubmit = async (e) => {
    e.preventDefault();
    const token = await RootServerApi.login(username, password)
    RootServerApi.accessToken = token.accessToken;
    localStorage.setItem("accessToken", token.accessToken);
    localStorage.setItem("expiresAt", token.expiresAt);
    navigate("/");
  }
    return <div>
    <form onSubmit={handleSubmit}>
        Username: <input type="text" onChange={handleUsernameChange} />
        Password: <input type="password" onChange={handlePasswordChange} />
        <button type="submit">Log In</button>
    </form>
</div>;
};
