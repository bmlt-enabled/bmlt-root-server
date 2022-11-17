import { Box, Button, FormControl, FormHelperText, InputLabel, MenuItem, TextField, Typography } from '@mui/material';
import Select, { SelectChangeEvent } from '@mui/material/Select';
import { styled } from '@mui/system';
import { User, UserCreate } from 'bmlt-root-server-client';
import { useEffect, useState } from 'react';
import { Controller, useForm } from 'react-hook-form';

import RootServerApi from '../RootServerApi';
import { strings } from '../localization';

export const Users = () => {
  const [currentSelection, setCurrentSelection] = useState<number>(-1);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [users, setUsers] = useState<User[]>([]);
  const [apiErrorMessage, setApiErrorMessage] = useState<string>('');
  const [apiSuccessMessage, setApiSuccessMessage] = useState<string>('');
  const [validationMessage, setValidationMessage] = useState<UserCreate>({
    username: '',
    displayName: '',
    password: '',
    type: '',
  });

  const {
    register,
    reset,
    handleSubmit,
    setValue,
    control,
    formState: { errors },
  } = useForm<UserCreate>();

  const StyledButtonWrapper = styled(Box)(({ theme }) => ({
    display: 'flex',
    justifyContent: 'center',
    marginTop: theme.spacing(2),
  }));

  const StyledInputWrapper = styled(FormControl)(({ theme }) => ({
    marginBottom: theme.spacing(4),
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  }));

  const StyledFormWrapper = styled(Box)(({ theme }) => ({
    width: '100%',
    padding: '20px',
    border: '1px solid #ccc',
    borderRadius: theme.shape.borderRadius,
    marginTop: '20px',
  }));

  const StyledFormLabel = styled(Typography)(({ theme }) => ({
    marginBottom: theme.spacing(2),
    color: theme.palette.primary.main,
  }));

  const handleUserChange = (event: SelectChangeEvent): void => {
    setCurrentSelection(parseInt(event.target.value));

    users.forEach((user) => {
      if (parseInt(event.target.value) === user.id) {
        setSelectedUser(user);
      } else if (parseInt(event.target.value) === -1) {
        setSelectedUser(null);
        reset();
      }
    });
  };

  const getUsers = async (): Promise<void> => {
    try {
      const allUsers = await RootServerApi.getUsers();
      setUsers(allUsers);
      console.log(allUsers);
    } catch (error: any) {
      RootServerApi.handleErrors(error, {
        handleError: (error) => {
          showErrorForFiveSeconds(error.message);
        },
      });
    }
  };

  const showErrorForFiveSeconds = (error: string): void => {
    setApiErrorMessage(error);
    setTimeout(() => {
      setApiErrorMessage('');
    }, 5000);
  };

  const showSuccessForFiveSeconds = (message: string): void => {
    setApiSuccessMessage(message);
    setTimeout(() => {
      setApiSuccessMessage('');
    }, 5000);
  };

  const applyChangesApiError = async (error: any): Promise<void> => {
    setValidationMessage({
      username: '',
      displayName: '',
      password: '',
      type: '',
    });
    await RootServerApi.handleErrors(error, {
      handleError: (error) => {
        showErrorForFiveSeconds(error.message);
      },
      handleValidationError: (error) =>
        setValidationMessage({
          ...validationMessage,
          username: (error?.errors?.username ?? []).join(' '),
          displayName: (error?.errors?.displayName ?? []).join(' '),
          password: (error?.errors?.password ?? []).join(' '),
          type: (error?.errors?.type ?? []).join(' '),
        }),
    });
  };

  const applyChanges = async (user: UserCreate): Promise<void> => {
    // "Create New User" is selected
    if (currentSelection === -1) {
      try {
        const newUser = await RootServerApi.createUser(user);
        console.log(newUser);
        reset();
        setUsers([...users, newUser]);
        showSuccessForFiveSeconds('User successfully created!');
      } catch (error: any) {
        applyChangesApiError(error);
      }
    } else {
      try {
        await RootServerApi.updateUser(currentSelection, user);
        getUsers();
        showSuccessForFiveSeconds('User successfully updated!');
        reset();
        setCurrentSelection(-1);
      } catch (error: any) {
        applyChangesApiError(error);
      }
    }
  };

  useEffect(() => {
    getUsers();
  }, []);

  useEffect(() => {
    if (selectedUser?.ownerId === null) {
      setValue('ownerId', '');
    } else {
      setValue('ownerId', selectedUser?.ownerId as string);
    }

    setValue('username', selectedUser?.username as string);
    setValue('displayName', selectedUser?.displayName as string);
    setValue('type', selectedUser?.type as string);
    setValue('email', selectedUser?.email as string);
    setValue('description', selectedUser?.description as string);
  }, [selectedUser]);

  return (
    <div>
      <Box>
        <FormControl>
          <InputLabel id='user-select-label'>{strings.userTitle}</InputLabel>
          <Select
            labelId='user-select-label'
            id='user-select'
            value={currentSelection.toString()}
            label={strings.userTitle}
            onChange={handleUserChange}
          >
            <MenuItem value={-1}>{strings.createNewUserTitle}</MenuItem>
            {users.map((currentUser, i) => {
              return (
                <MenuItem value={currentUser.id} key={i}>
                  {currentUser.displayName}
                </MenuItem>
              );
            })}
          </Select>
        </FormControl>
      </Box>
      {apiErrorMessage.length > 0 && <p style={{ color: 'red', textAlign: 'center' }}>{apiErrorMessage}</p>}
      {apiSuccessMessage.length > 0 && <p style={{ color: 'green', textAlign: 'center' }}>{apiSuccessMessage}</p>}
      <StyledFormWrapper>
        {currentSelection !== -1 && (
          <StyledFormLabel variant='h3' align='center'>
            {`${strings.userTitle} ${strings.idTitle} #${selectedUser?.id}`}
          </StyledFormLabel>
        )}
        <form onSubmit={handleSubmit(applyChanges)} noValidate>
          <StyledInputWrapper>
            <h3>{strings.userIsATitle}</h3>
            <FormControl fullWidth>
              <Controller
                name='type'
                control={control}
                defaultValue=''
                render={({ field: { onChange, value } }) => (
                  <Select
                    error={errors?.type?.type === 'required' || validationMessage?.type !== ''}
                    labelId='type-select-label'
                    id='type-select'
                    defaultValue=''
                    required
                    value={value}
                    {...register('type', { required: true })}
                    onChange={onChange}
                  >
                    <MenuItem value='admin'>Admin</MenuItem>
                    <MenuItem value='serviceBodyAdmin'>Service Body Administrator</MenuItem>
                    <MenuItem value='observer'>Service Body Observer</MenuItem>
                    <MenuItem value='disabled'>Disabled User</MenuItem>
                  </Select>
                )}
              />
            </FormControl>
            <FormHelperText id='type-error-text'>
              {(validationMessage?.type !== '' && validationMessage?.type) || (errors?.type?.type === 'required' && 'Type is required')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.ownedByTitle}</h3>
            <FormControl fullWidth>
              <Controller
                name='ownerId'
                control={control}
                defaultValue=''
                render={({ field: { onChange, value } }) => (
                  <Select
                    labelId='owner-id-label'
                    defaultValue=''
                    id='owner-id-select'
                    value={value}
                    {...register('ownerId', { required: false })}
                    onChange={onChange}
                  >
                    {users.map((currentUser, i) => {
                      return (
                        <MenuItem value={currentUser.id} key={i}>
                          {currentUser.displayName}
                        </MenuItem>
                      );
                    })}
                  </Select>
                )}
              />
            </FormControl>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.usernameTitle}</h3>
            <TextField
              error={errors?.username?.type === 'required' || validationMessage?.username !== ''}
              id='username'
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
          <StyledInputWrapper>
            <h3>{strings.nameTitle}</h3>
            <TextField
              error={errors?.displayName?.type === 'required' || validationMessage?.displayName !== ''}
              id='name'
              type='text'
              fullWidth
              required
              variant='outlined'
              aria-describedby='name-error-text'
              {...register('displayName', { required: true })}
            />
            <FormHelperText id='name-error-text'>
              {(validationMessage?.displayName !== '' && validationMessage?.displayName) ||
                (errors?.displayName?.type === 'required' && 'Name is required')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.emailTitle?.slice(0, -1)}</h3>
            <TextField
              id='email'
              type='text'
              fullWidth
              variant='outlined'
              aria-describedby='email-error-text'
              {...register('email', { required: false })}
            />
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.passwordTitle}</h3>
            <TextField
              error={errors?.password?.type === 'required' || validationMessage?.password !== ''}
              // TODO: Only make required if creating new user
              id='password'
              type='password'
              fullWidth
              required
              variant='outlined'
              aria-describedby='password-error-text'
              {...register('password', { required: true, minLength: 12 })}
            />
            <FormHelperText id='password-error-text'>
              {(validationMessage?.password !== '' && validationMessage?.password) ||
                (errors?.password?.type === 'required' && 'Password is required') ||
                (errors?.password?.type === 'minLength' && 'Password must be at least 12 characters')}
            </FormHelperText>
          </StyledInputWrapper>
          <StyledInputWrapper>
            <h3>{strings.descriptionTitle}</h3>
            <TextField
              id='description'
              type='text'
              fullWidth
              variant='outlined'
              aria-describedby='description-error-text'
              {...register('description', { required: false })}
            />
          </StyledInputWrapper>
          <StyledButtonWrapper sx={{ display: 'flex', justifyContent: 'center', marginTop: '' }}>
            <Button variant='contained' color='primary' type='submit'>
              {strings.applyChangesTitle}
            </Button>
          </StyledButtonWrapper>
        </form>
        {currentSelection !== -1 && (
          <StyledButtonWrapper sx={{ display: 'flex', justifyContent: 'center', marginTop: '' }}>
            <Button
              variant='contained'
              color='primary'
              onClick={async () => {
                try {
                  await RootServerApi.deleteUser(currentSelection);
                  setCurrentSelection(-1);
                  let newUsers = users.filter((user) => {
                    return user.id != currentSelection;
                  });
                  setUsers(newUsers);
                  reset();
                  showSuccessForFiveSeconds('User successfully deleted!');
                } catch (error: any) {
                  RootServerApi.handleErrors(error, {
                    handleError: (error) => {
                      showErrorForFiveSeconds(`Unable to delete user: ${error.message}`);
                    },
                  });
                }
              }}
            >
              {strings.deleteUserTitle}
            </Button>
          </StyledButtonWrapper>
        )}
      </StyledFormWrapper>
    </div>
  );
};
