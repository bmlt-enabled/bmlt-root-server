import {
  Box,
  Button,
  TextField,
  OutlinedInput,
  Typography,
  FormControl,
  InputLabel,
} from '@mui/material';
import { styled } from '@mui/system';
import { useForm, Controller } from 'react-hook-form';
import { FormSubmitError } from './errors/FormSubmitError';
import { InputRequiredError } from './errors/InputRequiredError';

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
    control,
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
            id='login-username'
            label='Username'
            type='text'
            fullWidth
            required
            variant='outlined'
            {...register('username', { required: true })}
          />
          {errors?.username?.type === 'required' && (
            <InputRequiredError message='Username is required' />
          )}
        </StyledInputWrapper>
        <StyledInputWrapper>
          <Controller
            name='password'
            control={control}
            rules={{ required: true }}
            render={({ field }) => (
              <FormControl variant='outlined' fullWidth>
                <InputLabel htmlFor='outlined-adornment-password'>Password</InputLabel>
                <OutlinedInput
                  {...field}
                  id='login-password'
                  label='Password'
                  type='password'
                  required
                  // {...register('password', { required: true })}
                />
              </FormControl>
            )}
          />
          {errors?.password?.type === 'required' && (
            <InputRequiredError message='Password is required' />
          )}
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
