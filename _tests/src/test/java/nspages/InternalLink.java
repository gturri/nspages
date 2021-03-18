package nspages;

public class InternalLink {
	private String dest;
	private String text;
	private String id;

	public InternalLink(String dest, String text) {
		this(dest, text, "");
    }
	public InternalLink(String dest, String text, String id){
		this.dest = dest;
		this.text = text;
		this.id = id;
	}

	public String dest(){
		return dest;
	}

	public String text(){
		return text;
	}

	public String id(){
		return id;
	}
}
