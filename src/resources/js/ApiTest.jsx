import React from 'react';
import RootServerApi from './RootServerApi'


class LoginFormTest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {username: '', password: '', isLoggedIn: RootServerApi.isLoggedIn}
    }

    handleUsernameChange = (e) => {
        this.setState({username: e.target.value});
    };

    handlePasswordChange = (e) => {
        this.setState({password: e.target.value});
    };

    handleLogout = async() => {
        await RootServerApi.logout();
        RootServerApi.accessToken = null;
        this.setState({username: '', password: '', isLoggedIn: RootServerApi.isLoggedIn});
    };

    handleSubmit = async (e) => {
        e.preventDefault();
        const token = await RootServerApi.login(this.state.username, this.state.password)
        RootServerApi.accessToken = token.accessToken;
        this.setState({isLoggedIn: RootServerApi.isLoggedIn});
    };

    render() {
        if (this.state.isLoggedIn) {
            return <button onClick={this.handleLogout}>Log Out</button>
        } else {
            return (
                <div>
                    <form onSubmit={this.handleSubmit}>
                        Username: <input type="text" onChange={this.handleUsernameChange} />
                        Password: <input type="password" onChange={this.handlePasswordChange} />
                        <button type="submit">Log In</button>
                    </form>
                </div>
            );
        }
    }
}

class GetMeetingsTest extends React.Component {
    constructor(props) {
        super(props);
        this.state = {numMeetings: null};
    }

    handleClick = async () => {
        const meetings = await RootServerApi.getMeetings();
        this.setState({numMeetings: meetings.length});
    };

    render() {
        const numMeetings = this.state.numMeetings;
        return (
            <div>
                <button onClick={this.handleClick}>Get Meetings</button>
                <div>
                    {numMeetings === null ? '' : 'There are ' + numMeetings + ' meetings.'}
                </div>
            </div>
        );
    }
}

export default class ApiTest extends React.Component {
    render() {
        return (
            <div>
                <LoginFormTest/>
                <br/><br/>
                <GetMeetingsTest/>
            </div>
        );
    }
}
