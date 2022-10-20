import { AppBar, Button, Container, Typography } from "@mui/material";
import { styled } from "@mui/system";
import React from "react";

const StyledAppBar = styled(AppBar)(({ theme }) => ({
  backgroundColor: theme.palette.tertiary.main,
  paddingTop: theme.spacing(2),
  paddingBottom: theme.spacing(2),
}));

export const Header = () => {
  return (
    <StyledAppBar position="relative">
      <Container maxWidth="lg">
        <Typography color="dark.main">Root Server Header</Typography>
      </Container>
      </StyledAppBar>
  );
};
