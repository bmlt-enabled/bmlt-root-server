import { useState, useContext } from 'react';
import RootServerApi from '../RootServerApi';
import { useNavigate } from 'react-router-dom';
import LoginForm from '../partials/forms/LoginForm';
import { SubmitHandler } from 'react-hook-form';
import { LoginLayout } from '../layouts/LoginLayout';
import { AppContext } from '../context/AppContext';
import { loadAccessToken } from '../helpers/utils';

type Props = {
  username: string;
  password: string;
  setUserName: React.Dispatch<React.SetStateAction<string>>;
};

export const Login = () => {
  const appContext = useContext(AppContext);
  const navigate = useNavigate();
  const [authenticationMessage, setAuthenticationMessage] = useState('');
  const [validationMessage, setValidationMessage] = useState({
    username: '',
    password: '',
  });
  const [notFoundMessage, setNotFoundMessage] = useState('');

  console.log('validationMessage', validationMessage);
  const handleOnSubmit: SubmitHandler<Props> = async ({ username, password }) => {
    try {
      const token = await RootServerApi.login(username, password);
      console.log(token);
      RootServerApi.token = token;
      localStorage.setItem('token', JSON.stringify(token));
      loadAccessToken();
      const LoggedInUserData = await RootServerApi.getUser(token.userId);
      appContext?.setUserName(LoggedInUserData.displayName);
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
        handleNotFoundError: () => {
          setNotFoundMessage("User doesn't exist");
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
        notFoundMessage={notFoundMessage}
      />
    </LoginLayout>
  );
};
