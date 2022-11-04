import { Box, Button, FormControl, FormHelperText, TextField, Typography } from '@mui/material';
import { styled } from '@mui/system';
import { useForm } from 'react-hook-form';

import { FormSubmitError } from './errors/FormSubmitError';

const StyledButtonWrapper = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  marginTop: theme.spacing(2),
}));

const StyledInputWrapper = styled(FormControl)(({ theme }) => ({
  marginBottom: theme.spacing(4),
}));

const StyledFormWrapper = styled(Box)(({ theme }) => ({
  width: '100%',
  padding: '20px',
  border: '1px solid #ccc',
  borderRadius: theme.shape.borderRadius,
}));

const StyledFormLabel = styled(Typography)(({ theme }) => ({
  marginBottom: theme.spacing(2),
  color: theme.palette.primary.main,
}));

type Props = {
  handleOnSubmit: (data: any) => void;
  authenticationMessage?: string;
  validationMessage?: {
    username?: string;
    password?: string;
  };
  notFoundMessage?: string;
};

const LoginForm = ({ handleOnSubmit, authenticationMessage, validationMessage }: Props) => {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm();

  return (
    <StyledFormWrapper>
      <StyledFormLabel variant='h3' align='center'>
        Login
      </StyledFormLabel>
      {authenticationMessage && <FormSubmitError message={authenticationMessage} />}
      <form onSubmit={handleSubmit(handleOnSubmit)} noValidate>
        <StyledInputWrapper fullWidth>
          <TextField
            error={errors?.username?.type === 'required' || validationMessage?.username !== ''}
            id='login-username'
            label='Username'
            type='text'
            fullWidth
            required
            variant='outlined'
            aria-describedby='username-error-text'
            {...register('username', { required: true })}
          />
          <FormHelperText id='username-error-text'>
            {(validationMessage?.username !== '' && validationMessage?.username) ||
              (errors?.username?.type === 'required' && 'Username is required')}
          </FormHelperText>
        </StyledInputWrapper>
        <StyledInputWrapper fullWidth>
          <TextField
            error={errors?.password?.type === 'required' || validationMessage?.password !== ''}
            id='login-password'
            label='Password'
            type='password'
            fullWidth
            required
            aria-describedby='password-error-text'
            {...register('password', { required: true })}
          />
          <FormHelperText id='password-error-text'>
            {(validationMessage?.password !== '' && validationMessage?.password) ||
              (errors?.password?.type === 'required' && 'Password is required')}
          </FormHelperText>
        </StyledInputWrapper>
        <StyledButtonWrapper sx={{ display: 'flex', justifyContent: 'center', marginTop: '' }}>
          <Button variant='contained' color='primary' type='submit'>
            Log In
          </Button>
        </StyledButtonWrapper>
      </form>
    </StyledFormWrapper>
  );
};

export default LoginForm;
