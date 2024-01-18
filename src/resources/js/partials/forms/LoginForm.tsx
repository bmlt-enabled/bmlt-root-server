import { Box, Button, FormHelperText, TextField } from '@mui/material';
import { styled } from '@mui/system';
import { useForm } from 'react-hook-form';

import { strings } from '../../localization';
import FormControlWrapper from './FormControlWrapper';
import FormWrapper from './FormWrapper';
import FormSubmitError from './errors/FormSubmitError';

const StyledButtonWrapper = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  marginTop: theme.spacing(2),
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
    <FormWrapper heading={`${strings.loginTitle}`}>
      {authenticationMessage && <FormSubmitError message={authenticationMessage} />}
      <form onSubmit={handleSubmit(handleOnSubmit)} noValidate>
        <FormControlWrapper>
          <TextField
            error={errors?.username?.type === 'required' || validationMessage?.username !== ''}
            id='login-username'
            label={strings.usernameTitle}
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
        </FormControlWrapper>
        <FormControlWrapper>
          <TextField
            error={errors?.password?.type === 'required' || validationMessage?.password !== ''}
            id='login-password'
            label={strings.passwordTitle}
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
        </FormControlWrapper>
        <StyledButtonWrapper sx={{ display: 'flex', justifyContent: 'center', marginTop: '' }}>
          <Button variant='contained' color='primary' type='submit'>
            {strings.loginVerb}
          </Button>
        </StyledButtonWrapper>
      </form>
    </FormWrapper>
  );
};

export default LoginForm;
