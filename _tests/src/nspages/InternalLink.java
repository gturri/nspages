package nspages;

public class InternalLink {
	private String dest;
	private String text;

	public InternalLink(String dest, String text){
		this.dest = dest;
		this.text = text;
	}

	public String dest(){
		return dest;
	}

	public String text(){
		return text;
	}
}
