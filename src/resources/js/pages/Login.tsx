import { useState } from 'react';
import RootServerApi from '../RootServerApi';
import { useNavigate } from 'react-router-dom';
import LoginForm from '../partials/forms/LoginForm';
import { SubmitHandler } from 'react-hook-form';
// import { handleApiErrors } from '../helpers/handleApiErrors';
import { LoginLayout } from '../layouts/LoginLayout';
import { AuthenticationError, ValidationError } from 'bmlt-root-server-client';

type formValues = {
  username: string;
  password: string;
};

export const Login = () => {
  const navigate = useNavigate();
  const [errorMessage, setErrorMessage] = useState('');

  const handleAuthenticationError = (error: AuthenticationError) => {
    setErrorMessage(error.message);
  };

  const handleValidationError = (error: ValidationError) => {
    console.log('validation error', error.errors);
  };

  const handleError = ((error: any) => {
    console.log('other error', error);
  });

  const handleOnSubmit: SubmitHandler<formValues> = async (inputValues) => {
    try {
      const token = await RootServerApi.login(inputValues.username, inputValues.password);
      console.log(token);
      RootServerApi.accessToken = token.accessToken ?? null;
      localStorage.setItem('token', JSON.stringify(token));
      navigate('/');
    } catch (error: any) {
      await RootServerApi.handleErrors({error, handleAuthenticationError, handleValidationError, handleError});
    }
  };

  return (
    <LoginLayout>
      <LoginForm handleOnSubmit={handleOnSubmit} errorMessage={errorMessage} />
    </LoginLayout>
  );
};
