import React, { useState } from "react";
import RootServerApi from "../RootServerApi";
import {useNavigate} from "react-router-dom";
import { Header } from "../sections/Header";
import { LoginForm } from "../partials/forms/LoginForm";
import { Box, Button } from "@mui/material";
import { styled } from "@mui/system";

const StyledFormWrapper = styled(Box)(({theme}) => ({
  position: "absolute",
  top: "50%",
  left: "50%",
  transform: "translate(-50%, -50%)",
  width: "100%",
  maxWidth: "600px",
  padding: "20px",
  border: "1px solid #ccc",
  borderRadius: theme.borderRadius,
}));


export const Login = () => {
  const navigate = useNavigate();
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");

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
    return (
    <div>
      <Header />
      <StyledFormWrapper>
      <LoginForm
        handlePasswordChange={handlePasswordChange}
        handleUsernameChange={handleUsernameChange}
        handleSubmit={handleSubmit}
      />
    </StyledFormWrapper>
</div>)
};
