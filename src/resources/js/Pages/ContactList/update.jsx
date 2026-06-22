import {useRef, useState, useEffect} from 'react';
import { useNavigate, useLocation, Link, useParams } from "react-router";
import UpdateContactListButton from '../../Components/ContactList/UpdateContactListButton.jsx';
import useAxiosPrivate from "../../Hooks/useAxiosPrivate";

export default function UpdateContactList() {
    const nameRef = useRef();
    const navigate = useNavigate();
    const location = useLocation();
    const axiosPrivate = useAxiosPrivate();
    const params = useParams();
    const [name, setName] = useState(location.state?.name || '');
    const [errMsg, setErrMsg] = useState('');
    const [succMsg, setSuccMsg] = useState('');

    useEffect(() => {
        nameRef.current.focus();
    }, [])

    useEffect(() => {
        setErrMsg('');
    }, [name])

    const handleSubmit = async (e) => {
        e.preventDefault(); // ← blocca il comportamento default del form        
        const controller = new AbortController();
        try {
            const response = await axiosPrivate.patch('/contact-lists/' + params.id, 
            {
                name: name,
            },
            {
                signal: controller.signal
            });
            setSuccMsg('List updated');
            console.log(response?.data);
        } catch(err) {
            if(!err?.response) {
                setErrMsg('No Server response');
                console.error(err);
            } else if (err?.response?.status) {
                setErrMsg(err?.response?.status + ' ' + err?.response?.data?.message);
                console.error(err);
                navigate('/login', { state: { from: location }, replace: true });
            }
        }    
    }

    return (
            <div className="UpdateContactList">
                    <section>
                        <h1>Update List</h1>
                        <h2>Messages</h2>
                            <p className={errMsg ? "errmsg" : "offscreen"}  aria-live="assertive">{errMsg}</p>
                            <p className={succMsg ? "succmsg" : "offscreen"}  aria-live="assertive">{succMsg}</p>
                        <h2>Form</h2>
                            <form onSubmit={handleSubmit}>
                                <label htmlFor="name">name</label>
                                <input 
                                    type="text" 
                                    id="name"
                                    ref={nameRef}
                                    autoComplete="off"
                                    onChange={(e) =>  setName(e.target.value)}
                                    value={name}
                                    required
                                />                                
                                <UpdateContactListButton />                                
                            </form>
                            <br />
                        <h2>Links</h2>
                            <div className="flexGrow">
                                <Link to="/contact-lists">Go to the lists page</Link>
                            </div>
                            <div className="flexGrow">
                                <Link to="/">Home</Link>
                            </div>
                    </section>            
            </div>
        );
}
