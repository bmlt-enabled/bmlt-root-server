import { Typography } from '@mui/material';

interface IProps {
  message: string | null;
}

export const InputRequiredError = ({ message }: IProps) => {
  return (
    <Typography variant='caption' color='error.main'>
      {message}
    </Typography>
  );
};
