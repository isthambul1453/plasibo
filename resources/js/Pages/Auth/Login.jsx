import { useEffect, useState } from "react";
import Checkbox from "@/Components/Checkbox";
import GuestLayout from "@/Layouts/GuestLayout";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import { Head, Link, useForm } from "@inertiajs/react";
import axios from "axios";

export default function Login({ status, errorMessage, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    const [callStatus, setCallStatus] = useState(null);
    const [callLoading, setCallLoading] = useState(false);

    useEffect(() => {
        return () => {
            reset("password");
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();
        post(route("login"));
    };

    const requestCall = async () => {
        setCallLoading(true);
        setCallStatus(null);
        try {
            const res = await axios.post(route("retell.call"));
            setCallStatus({ type: "success", message: res.data.message });
        } catch (err) {
            const msg = err.response?.data?.error || "Something went wrong.";
            setCallStatus({ type: "error", message: msg });
        } finally {
            setCallLoading(false);
        }
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            {status && (
                <div className="mb-4 font-medium text-sm text-green-600">
                    {status}
                </div>
            )}
            {errorMessage && (
                <div className="mb-4 font-medium text-sm text-red-600">
                    {errorMessage}
                </div>
            )}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData("email", e.target.value)}
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        onChange={(e) => setData("password", e.target.value)}
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="block mt-4">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData("remember", e.target.checked)}
                        />
                        <span className="ms-2 text-sm text-gray-600 dark:text-gray-400">
                            Remember me
                        </span>
                    </label>
                </div>

                <div className="flex items-center justify-end mt-4">
                    {canResetPassword && (
                        <Link
                            href={route("password.request")}
                            className="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        >
                            Forgot your password?
                        </Link>
                    )}
                    <PrimaryButton className="ms-4" disabled={processing}>
                        Log in
                    </PrimaryButton>
                </div>
            </form>

            <div className="mt-6 border-t pt-4 dark:border-gray-700">
                <p className="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    Forgot your credentials? We can call you.
                </p>
                <button
                    type="button"
                    onClick={requestCall}
                    disabled={callLoading}
                    className="w-full flex justify-center items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-medium rounded-md transition"
                >
                    {callLoading ? (
                        <>
                            <svg className="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            Calling...
                        </>
                    ) : (
                        <span>&#128222; Call me with my credentials</span>
                    )}
                </button>

                {callStatus && (
                    <p className={`mt-2 text-sm ${
                        callStatus.type === "success" ? "text-green-600" : "text-red-600"
                    }`}>
                        {callStatus.message}
                    </p>
                )}
            </div>
        </GuestLayout>
    );
}
