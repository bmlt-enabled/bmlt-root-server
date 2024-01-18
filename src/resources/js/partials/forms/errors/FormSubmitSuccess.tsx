import { Box, Typography } from '@mui/material';
import { styled } from '@mui/system';

const StyledSuccessMessage = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  marginBottom: theme.spacing(2),
}));

type Props = {
  message: string | null;
};

const FormSubmitSuccess = ({ message }: Props) => {
  return (
    <StyledSuccessMessage>
      <Typography variant='caption' color='success.main'>
        {message}
      </Typography>
    </StyledSuccessMessage>
  );
};

export default FormSubmitSuccess;
