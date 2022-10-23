import { useState } from 'react';
import RootServerApi from '../RootServerApi';
import { useNavigate } from 'react-router-dom';
import LoginForm from '../partials/forms/LoginForm';
import { SubmitHandler } from 'react-hook-form';
import { handleApiErrors } from '../helpers/handleApiErrors';
import { LoginLayout } from '../layouts/LoginLayout';

interface IFormValues {
  username: string;
  password: string;
}

export const Login = () => {
  const navigate = useNavigate();
  const [errorMessage, setErrorMessage] = useState('');

  const handleOnSubmit: SubmitHandler<IFormValues> = async (inputValues) => {
    try {
      const token = await RootServerApi.login(inputValues.username, inputValues.password);
      console.log(token);
      RootServerApi.accessToken = token.accessToken ?? null;
      localStorage.setItem('token', JSON.stringify(token));
      navigate('/');
    } catch (error: any) {
      const errorStatus = await handleApiErrors(error);
      setErrorMessage(errorStatus);
    }
  };

  return (
    <LoginLayout>
      <LoginForm handleOnSubmit={handleOnSubmit} errorMessage={errorMessage} />
    </LoginLayout>
  );
};
