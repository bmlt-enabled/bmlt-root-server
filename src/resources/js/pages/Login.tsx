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
  const [validationMessage, setValidationMessage] = useState({
    username: '',
    password: '',
  });

  const handleAuthenticationError = (error: AuthenticationError) => {
    setErrorMessage(error.message);
  };

  const handleValidationError = (error: ValidationError) => {
    setValidationMessage(
      error.errors.username && {
        ...validationMessage,
        username: error.errors.username ? error.errors.username.join(' ') : '',
        password: error.errors.password ? error.errors.password.join(' ') : '',
      },
    );
  };

  const handleError = (error: any) => {
    console.log('other error', error);
  };

  console.log('validationMessage', validationMessage);
  const handleOnSubmit: SubmitHandler<formValues> = async (inputValues) => {
    try {
      const token = await RootServerApi.login(inputValues.username, inputValues.password);
      console.log(token);
      RootServerApi.accessToken = token.accessToken ?? null;
      localStorage.setItem('token', JSON.stringify(token));
      navigate('/');
    } catch (error: any) {
      await RootServerApi.handleErrors({
        error,
        handleAuthenticationError,
        handleValidationError,
        handleError,
      });
    }
  };

  return (
    <LoginLayout>
      <LoginForm
        handleOnSubmit={handleOnSubmit}
        errorMessage={errorMessage}
        validationMessage={validationMessage}
      />
    </LoginLayout>
  );
};
