import { Typography } from '@mui/material';

type props = {
  message: string | null;
};

export const InputRequiredError = ({ message }: props) => {
  return (
    <Typography variant='caption' color='error.main'>
      {message}
    </Typography>
  );
};
