import React from 'react';
import { createRoot } from 'react-dom/client'
import { Configuration, RootServerApi } from "bmlt-root-server-client/src";

// the actual url is defined in the frontend.blade.php template
let api = new RootServerApi(new Configuration({ basePath: baseUrl }));

class LoginButton extends React.Component {
    handleClick = async () => {
        const tokenCredentials = { username: 'serveradmin', password: 'CoreysGoryStory' };
        const authTokenRequest = { tokenCredentials: tokenCredentials };
        const token = await api.authToken(authTokenRequest);
        api = new DefaultApi(new Configuration({ basePath: baseUrl, accessToken: () => token.accessToken }))
        console.log(token);
    };

    render() {
        return (
            <button onClick={this.handleClick}>Get Token</button>
        );
    }
}

class GetMeetingsButton extends React.Component {
    handleClick = async () => {
        const meetings = api.getMeetings();
        console.log(meetings);
    };

    render() {
        return (
            <button onClick={this.handleClick}>Get Meetings</button>
        );
    }
}

export default function App(){
    return(
        <div>
            <LoginButton/>
            <GetMeetingsButton/>
        </div>
    );
}

if(document.getElementById('root')){
    createRoot(document.getElementById('root')).render(<App />)
}


