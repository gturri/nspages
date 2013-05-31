package nspages.printers;

import nspages.Helper;

import org.junit.Test;

public class T_simpleline extends Helper {
	@Test
	public void simpleLinePrinter(){
		generatePage("simpleline:start", "<nspages -simpleLine -subns>");
		//TODO
	}

	public void withSubnsAndEmptyTextPageEverythingIsOnASingeLine(){
		//TODO	<nspages -simpleLine -subns -textPages="">
	}

	public void withSubnsAndEmptyTextPageButWithoutWantingPagesThereIsNoEndingComma(){
		//TODO <nspages -simpleLine -subns -textPages="" -nopages>
	}

	public void withEmptyTextPageButWithoutNsThereIsNoOpeningComma(){
		//TODO <nspages -simpleLine -textPages="">
	}

	public void withSubnsAndEmptyTextPageButWithoutActualPagesThereIsNoEndingComma(){
		//TODO - <nspages -simpleLine -subns -exclude:p1 -exclude:p2 -exclude -textPages="">
	}
}
