import { Box, Button, TextField } from '@mui/material';
import { styled } from '@mui/system';
import React from 'react';

const StyledButtonWrapper = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  marginTop: theme.spacing(2),
}));

const StyledTextField = styled(TextField)(({ theme }) => ({
  marginBottom: theme.spacing(),
}));

type Props = {
  handleSubmit: React.FormEventHandler<HTMLFormElement>;
  handlePasswordChange: React.ChangeEventHandler<HTMLInputElement>;
  handleUsernameChange: React.ChangeEventHandler<HTMLInputElement>;
};

export const LoginForm = ({ handleSubmit, handlePasswordChange, handleUsernameChange }: Props) => {
  return (
    <form onSubmit={handleSubmit}>
      <StyledTextField
        id='login-username'
        label='Username'
        type='text'
        size='small'
        fullWidth
        onChange={handleUsernameChange}
        variant='outlined'
      />
      <StyledTextField
        id='login-password'
        label='Password'
        type='password'
        size='small'
        fullWidth
        onChange={handlePasswordChange}
        autoComplete='current-password'
        variant='outlined'
      />
      <StyledButtonWrapper sx={{ display: 'flex', justifyContent: 'center', marginTop: '' }}>
        <Button variant='contained' color='primary' type='submit'>
          Log In
        </Button>
      </StyledButtonWrapper>
    </form>
  );
};
