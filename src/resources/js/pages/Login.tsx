import { useState } from 'react';
import RootServerApi from '../RootServerApi';
import { useNavigate } from 'react-router-dom';
import LoginForm from '../partials/forms/LoginForm';
import { SubmitHandler } from 'react-hook-form';
import { LoginLayout } from '../layouts/LoginLayout';

type Props = {
  username: string;
  password: string;
  setUserName: React.Dispatch<React.SetStateAction<string>>;
};

export const Login = () => {
  // const appContext = useAppContext();
  const navigate = useNavigate();
  const [authenticationMessage, setAuthenticationMessage] = useState('');
  const [validationMessage, setValidationMessage] = useState({
    username: '',
    password: '',
  });

  console.log('validationMessage', validationMessage);
  const handleOnSubmit: SubmitHandler<Props> = async ({ username, password }) => {
    try {
      const token = await RootServerApi.login(username, password);
      localStorage.setItem('token', JSON.stringify(token));
      RootServerApi.token = token;
      navigate('/');
    } catch (error: any) {
      setValidationMessage({
        username: '',
        password: '',
      });
      setAuthenticationMessage('');
      await RootServerApi.handleErrors({
        error,
        handleAuthenticationError: (error) => setAuthenticationMessage(error.message),
        handleValidationError: (error) => {
          setValidationMessage({
            ...validationMessage,
            username: (error?.errors?.username ?? []).join(' '),
            password: (error?.errors?.password ?? []).join(' '),
          });
        },
        handleError: (error) => console.log('other error', error),
      });
    }
  };

  return (
    <LoginLayout>
      <LoginForm
        handleOnSubmit={handleOnSubmit}
        authenticationMessage={authenticationMessage}
        validationMessage={validationMessage}
      />
    </LoginLayout>
  );
};
