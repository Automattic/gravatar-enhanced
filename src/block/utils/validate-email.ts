export default function validateEmail( email: string ): boolean {
	return /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test( email );
}
