import { Box, Button, TextField, Typography } from '@mui/material';
import { styled } from '@mui/system';
import { useForm } from 'react-hook-form';
import { FormSubmitError } from './errors/FormSubmitError';

const StyledButtonWrapper = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  marginTop: theme.spacing(2),
}));

const StyledInputWrapper = styled(Box)(({ theme }) => ({
  marginBottom: theme.spacing(2),
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

type props = {
  handleOnSubmit: (data: any) => void;
  errorMessage?: string;
};

const LoginForm = ({ handleOnSubmit, errorMessage }: props) => {
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
      {errorMessage && <FormSubmitError message={errorMessage} />}
      <form onSubmit={handleSubmit(handleOnSubmit)} noValidate>
        <StyledInputWrapper>
          <TextField
            error={errors?.username?.type === 'required'}
            id='login-username'
            label='Username'
            type='text'
            fullWidth
            // required
            variant='outlined'
            helperText='Username is required'
            {...register('username', { required: false })}
          />
        </StyledInputWrapper>
        <StyledInputWrapper>
          <TextField
            error={errors?.password?.type === 'required'}
            id='login-password'
            label='Password'
            type='password'
            fullWidth
            // required
            helperText='Password is required'
            {...register('password', { required: false })}
          />
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
